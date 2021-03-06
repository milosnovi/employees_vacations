<?php

namespace Ates\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ates\VacationBundle\Form\Type\HolidaysType;
use Ates\VacationBundle\Entity\Holidays;
use Ates\VacationBundle\Entity\VacationRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\Response;
use Ates\UserBundle\Form\Type\EditUserType;

use Symfony\Component\Console\Output\OutputInterface;


class AdminController extends Controller
{
    
    /**
     * @Route("/admin", name="show_admin_panel")
     * @Template("AtesUserBundle:Admin:panel.html.twig", vars={"requests","holidays","users"})
     */
    public function showAdminAction()
    {      
        $em = $this->getDoctrine()->getManager();
        
        $pagerfanta = $this->container->get('vacation_request.model')->getPendingRequests();
          
        $holidays = $em->getRepository('AtesVacationBundle:Holidays')->findAll();
        
        //get all user with confirmed email ( for account approving )
        $users = $em->getRepository('AtesUserBundle:User')->findBy(array(
            'enabled' => true,
            'locked' => true
        ));
        
        $activeUser = $this->getUser();
        $roles = $activeUser->getRoles();
        
        return array(                    
                'requests' => $pagerfanta,
                'holidays' => $holidays,
                'users' => $users,
                'user' => $activeUser,
                'roles' => $roles
            );
    }
    
    /**
     * @Route("/admin/approve_request/{id}", name="approve_request")
     * @Template("AtesUserBundle:Admin:panel.html.twig", vars={"requests","holidays","users"})
     */
    public function approveRequestAction($id)
    {
        
         $em = $this->getDoctrine()->getManager();
         $vacationRepository = $em->getRepository('AtesVacationBundle:VacationRequest');
         $vacationRequest = $vacationRepository->find($id);
         $userRepository = $em->getRepository('AtesUserBundle:User');

         $user = $userRepository->find($vacationRequest->getUser()->getId());

         $workingDays = $vacationRequest->getNumberOfWorkingDays();

         $vacationRequest->setState(VacationRequest::APPROVED);

         $noDaysOffLastYear = $user->getNoDaysOffLastYear();
         if($noDaysOffLastYear > 0)
         {
             if($noDaysOffLastYear >= $workingDays)
             {
                 $noDaysOffLastYear -= $workingDays;
                 $workingDays = 0;
             }            
             else
             {
                 $workingDays -= $noDaysOffLastYear;
                 $noDaysOffLastYear = 0;                 
             }
         }         
         
         $user->setNoDaysOffLastYear($noDaysOffLastYear);
         $user->setNoDaysOff($user->getNoDaysOff() - $workingDays);
               
         $em->flush();
          
         $this->sendEmail($user, $vacationRequest, VacationRequest::APPROVED);

         return $this->redirect($this->generateUrl('show_admin_panel'));
    }
    
    
    /**
     * @Route("/admin/approve_user/{id}", name="approve_user")
     */
    public function approveUserAction($id) // and sending slava request
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AtesUserBundle:User');
        $user = $repository->find($id);
        
        $user->setLocked(false);
        
        $vacationRequest = new VacationRequest();
        $today = new \DateTime("now");
        $date_of_slava = $user->getDateOfSlava();
        $date_of_slava_ends = new \DateTime($user->getDateOfSlava()->format('Y-m-d'));
        $date_of_slava_ends->modify('+1 day');

        $vacationRequest->setStartDate($date_of_slava); 
        $vacationRequest->setEndDate($date_of_slava_ends);
        $vacationRequest->setUser($user);
        $vacationRequest->setSubmitted($today);
        $vacationRequest->setState(VacationRequest::APPROVED);
        $vacationRequest->setEditTime($today);
        $vacationRequest->setComment('slava');

        $em = $this->container->get('doctrine')->getManager();
        $em->persist($vacationRequest);
        
        $em->flush();
        
        return $this->redirect($this->generateUrl('show_admin_panel'));
        
    }
    
    /**
     * @Route("/admin/delete_user_on_approving/{id}", name="delete_user_on_approving")
     * @Route("/admin/delete_user_on_approving", name="delete_user_on_approving_base")
     */
    public function deleteUserOnApprovingAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AtesUserBundle:User');
        $user = $repository->find($id);
        
        $em->remove($user);
        $em->flush();
        
        return $this->redirect($this->generateUrl('show_admin_panel'));
    }
    
    /**
     * @Route("/admin/reject_request/{id}", name="reject_request")
     * @Route("/admin/reject_request", name="reject_request_base")
     */
    public function rejectRequestAction($id)
    {
         $em = $this->getDoctrine()->getManager();
         $repository = $em->getRepository('AtesVacationBundle:VacationRequest');
         $vacationRequest = $repository->find($id);
          
         $vacationRequest->setState(VacationRequest::REJECTED);
          
         $em->flush();
         
         
        //send email
        $user = $em->getRepository('AtesUserBundle:User')->find($vacationRequest->getUser()->getId());
        $this->sendEmail($user, $vacationRequest, 'reject');
          
         return $this->redirect($this->generateUrl('show_admin_panel'));
    }
    
    /**
     * @Route("/admin/add_holiday", name="add_holiday")
     * @Template("AtesUserBundle:Admin:holidayForm.html.twig", vars={"form"})
     */
    public function addHolidayAction()
    {
         $form = $this->createForm(new HolidaysType());
         
          $request = $this->getRequest();
          $form->handleRequest($request);
       
          if($form->isValid()) 
          {                  
              $holiday = new Holidays();;               
              $holiday = $form->getData();
               
              $em = $this->getDoctrine()->getManager();
              $em->persist($holiday);
              $em->flush();
              
              return $this->redirect($this->generateUrl('show_admin_panel'));
          }
          
          return array(
              'form' => $form->createView(), 
              'user' => $this->getUser(), 
              'roles' => $this->getUser()->getRoles() );
    }
        
    /**
     * @Route("/admin/delete_holiday/{id}", name="delete_holiday")
     * @Route("/admin/delete_holiday", name="delete_holiday_base")
     */
    public function deleteHolidayAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        
        $em->remove($em->getRepository('AtesVacationBundle:Holidays')->find($id));
        $em->flush();
        
        return $this->redirect($this->generateUrl('show_admin_panel'));
    }

    /**
     * @Route("/admin/edit_holiday/{id}", name="edit_holiday_form")
     * @Template("AtesUserBundle:Admin:holidayForm.html.twig", vars={"form"})
     */
    public function editHolidayAction($id)
    {
        $form = $this->createForm(new HolidaysType());
        $em = $this->getDoctrine()->getManager();
        $holiday = $em->getRepository('AtesVacationBundle:Holidays')->find($id);
        
        $request = $this->getRequest();
        $form->handleRequest($request);
       
        if($form->isValid()) 
        {   
            $holiday->setName($form->get('name')->getData());
            $holiday->setDate($form->get('date')->getData());
             
            $em->flush();
             
            return $this->redirect($this->generateUrl('show_admin_panel'));
        }                  
        $form->setData($holiday);
              
        $activeUser = $this->getUser();
        $roles = $activeUser->getRoles();
        return array(
            'form' => $form->createView(),
            'user' => $activeUser,
            'roles' => $roles
        );
    }
    

    public function getWorkingDays($days,$startDate,$endDate,$holidays)
    {                     
        //floor — Round fractions down
        //fmod — Returns the floating point remainder (modulo) of the division of the arguments
        $no_full_weeks = floor($days / 7);
        $no_remaining_days = fmod($days, 7);

        //It will return 1 if it's Monday,.. ,7 for Sunday
        $the_first_day_of_week = $startDate->format('w');
        $the_last_day_of_week = $endDate->format('w');
                
        if ($the_first_day_of_week <= $the_last_day_of_week) 
        {
            if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) $no_remaining_days--;
            if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) $no_remaining_days--;
        }
        else
        {
            if ($the_first_day_of_week == 7) 
            {
                // if the start date is a Sunday, then we definitely subtract 1 day
                $no_remaining_days--;

                if ($the_last_day_of_week == 6)
                {
                    // if the end date is a Saturday, then we subtract another day
                    $no_remaining_days--;
                }
            }
            else
            {
                // the start date was a Saturday (or earlier), and the end date was (Mon..Fri)
                // so we skip an entire weekend and subtract 2 days
                $no_remaining_days -= 2;
            }
        }                
        $workingDays = $no_full_weeks * 5;
        if ($no_remaining_days > 0 )
        {
            $workingDays += $no_remaining_days;
        }

        $startDate = strtotime($startDate->format('T-m-d H:i:s'));
        $endDate = strtotime($endDate->format('T-m-d H:i:s'));
        
        //We subtract the holidays
        foreach($holidays as $holiday)
        {         
            $holidayDayOfTheWeek = $holiday->format('w');
            $time_stamp=strtotime($holiday->format('T-m-d H:i:s'));
            //If the holiday doesn't fall in weekend
            if ($startDate <= $time_stamp 
                    && $time_stamp <= $endDate 
                    && $holidayDayOfTheWeek != 6 
                    && $holidayDayOfTheWeek != 7)
            {
                $workingDays--;
            }
        }         
        return $workingDays;
    }
    
     /**
     * @Route("/admin/edit_user/{id}", name="admin_edit_user")
     * @Template("AtesUserBundle:Admin:editUser.html.twig", vars={"form"})
     */
    public function editUserAction($id)
    {   
        
        $user = $this->getDoctrine()->getManager()->getRepository('AtesUserBundle:User')->find($id);
        $form = $this->createForm(new EditUserType(), $user);
        
        $request = $this->getRequest();
        $form->handleRequest($request);
        
        if($form->isValid())
        {
            //return new Response('editovano');
            $em = $this->container->get('doctrine')->getManager();
            $em->persist($user);
            $em->flush();
            
            return $this->redirect($this->generateUrl('show_admin_panel'));
        }
        
        $activeUser = $this->getUser();
        $roles = $activeUser->getRoles();
        return array(
            'form' => $form->createView(),
            'user' => $activeUser,
            'roles' => $roles
        );
    }
    
    /**
     * @Route("/admin/promote_user/{id}", name="admin_promote_user")
     */
    public function promoteUserAction($id)
    {
        //dodeli role super admina
        $user = $this->container->get('doctrine')->getRepository('AtesUserBundle:User')->find($id);
        $username = $user->getUsername();
        
        $manipulator = $this->container->get('fos_user.util.user_manipulator');
        $manipulator->promote($username);
        
        return $this->redirect($this->generateUrl('show_admin_panel'));
    }
    
    function sendEmail($user, $vacationRequest, $requestState)
    {        
        $message = \Swift_Message::newInstance()
            ->setSubject('Ates - Vacation Request')
            ->setFrom('vacations@ates.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                    'AtesUserBundle:Admin:emaiRequestAction.html.twig',
                    array(
                        'user' => $user,
                        'request' => $vacationRequest,
                        'request_state' => $requestState
                    )
                )
            )
        ;
        $this->get('mailer')->send($message);
    }
   
}