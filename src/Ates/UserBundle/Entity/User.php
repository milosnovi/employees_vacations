<?php
    namespace Ates\UserBundle\Entity;
    
    use FOS\UserBundle\Model\User as BaseUser;
    use Doctrine\ORM\Mapping as ORM;
    use Doctrine\Common\Collections\ArrayCollection;
    use Gedmo\Mapping\Annotation as Gedmo;
    use Symfony\Component\Validator\Constraints as Assert;

    /**
     * @ORM\Entity
     * @ORM\Table(name="employee")
     */
    
    class User extends BaseUser
    {
        /**
        * @ORM\Id
        * @ORM\Column(type="integer")
        * @ORM\GeneratedValue(strategy="AUTO")
        */
        protected $id;

        /**
        * @ORM\Column(type="string", length=50)
        * @Assert\Length(
        *      min = "2",
        *      max = "50",
        *      minMessage = "Your first name must be at least {{ limit }} characters length",
        *      maxMessage = "Your first name cannot be longer than {{ limit }} characters length"
        * )
        */
        protected $first_name;

        /**
        * @ORM\Column(type="string", length=50)
        * @Assert\Length(
        *      min = "2",
        *      max = "50",
        *      minMessage = "Your first name must be at least {{ limit }} characters length",
        *      maxMessage = "Your first name cannot be longer than {{ limit }} characters length"
        * )
        */
        protected $last_name;

        /**
         * @ORM\Column(type="string", length=20)
         * @Assert\NotBlank()
         */
        protected $ssn;

        /**
         * @ORM\Column(type="string", length=50)
         * @Assert\NotBlank()
         */
        protected $address;

        /**
         * @ORM\Column(type="string", length=20)
         * @Assert\NotNull()
         */
        protected $phone;

        /**
         * @ORM\Column(type="date")
         * @Assert\DateTime()
         */
        protected $date_of_employment;

        /**
         * @ORM\Column(type="integer")
         * @Assert\Range(
         *      min = 0,
         *      max = 100,
         *      minMessage = "Minimum number of days off is 0!",
         *      maxMessage = "Maximum number of days off is 100!"
         * )
         */
        protected $no_days_off;
        
        /**
         * @ORM\Column(type="date")
         * @Assert\DateTime()
         */
        protected $date_of_slava;
        
        /**
         * @ORM\Column(type="integer")
         *
         */
        protected $no_days_off_last_year;

        /**
         *
         * @ORM\OneToMany(targetEntity="Ates\VacationBundle\Entity\VacationRequest", mappedBy="user")
         */
        protected $vacation_requests;

        /**
         * @Gedmo\Timestampable(on="create")
         * @ORM\Column(name="created", type="datetime")
         */
        protected $created;

        /**
         * @ORM\Column(name="updated", type="datetime")
         * @Gedmo\Timestampable(on="update")
         */
        private $updated;
        
    public function __construct()
    {
        parent::__construct();
        // your own logic
        $this->is_approved = false;
        $this->is_validated = false;
        $this->no_days_off = 20;
        $this->no_days_off_last_year = 0;
        $this->locked = true;
    }
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set first_name
     *
     * @param string $firstName
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->first_name = $firstName;
    
        return $this;
    }

    /**
     * Get first_name
     *
     * @return string 
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Set last_name
     *
     * @param string $lastName
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->last_name = $lastName;
    
        return $this;
    }

    /**
     * Get last_name
     *
     * @return string 
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Set ssn
     *
     * @param string $ssn
     * @return User
     */
    public function setSsn($ssn)
    {
        $this->ssn = $ssn;
    
        return $this;
    }

    /**
     * Get ssn
     *
     * @return string 
     */
    public function getSsn()
    {
        return $this->ssn;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return User
     */
    public function setAddress($address)
    {
        $this->address = $address;
    
        return $this;
    }

    /**
     * Get address
     *
     * @return string 
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return User
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    
        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set date_of_employment
     *
     * @param \DateTime $dateOfEmployment
     * @return User
     */
    public function setDateOfEmployment($dateOfEmployment)
    {
        $this->date_of_employment = $dateOfEmployment;
    
        return $this;
    }

    /**
     * Get date_of_employment
     *
     * @return \DateTime 
     */
    public function getDateOfEmployment()
    {
        return $this->date_of_employment;
    }


    /**
     * Set no_days_off
     *
     * @param integer $noDaysOff
     * @return User
     */
    public function setNoDaysOff($noDaysOff)
    {
        $this->no_days_off = $noDaysOff;
    
        return $this;
    }

    /**
     * Get no_days_off
     *
     * @return integer 
     */
    public function getNoDaysOff()
    {
        return $this->no_days_off;
    }


    /**
     * Set date_of_slava
     *
     * @param \DateTime $dateOfSlava
     * @return User
     */
    public function setDateOfSlava($dateOfSlava)
    {
        $this->date_of_slava = $dateOfSlava;
    
        return $this;
    }

    /**
     * Get date_of_slava
     *
     * @return \DateTime 
     */
    public function getDateOfSlava()
    {
        return $this->date_of_slava;
    }


    /**
     * Set no_days_off_last_year
     *
     * @param integer $noDaysOffLastYear
     * @return User
     */
    public function setNoDaysOffLastYear($noDaysOffLastYear)
    {
        $this->no_days_off_last_year = $noDaysOffLastYear;
    
        return $this;
    }

    /**
     * Get no_days_off_last_year
     *
     * @return integer 
     */
    public function getNoDaysOffLastYear()
    {
        return $this->no_days_off_last_year;
    }

    /**
     * Add vacation_requests
     *
     * @param \Ates\VacationBundle\Entity\VacationRequest $vacationRequests
     * @return User
     */
    public function addVacationRequest(\Ates\VacationBundle\Entity\VacationRequest $vacationRequests)
    {
        $this->vacation_requests[] = $vacationRequests;
    
        return $this;
    }

    /**
     * Remove vacation_requests
     *
     * @param \Ates\VacationBundle\Entity\VacationRequest $vacationRequests
     */
    public function removeVacationRequest(\Ates\VacationBundle\Entity\VacationRequest $vacationRequests)
    {
        $this->vacation_requests->removeElement($vacationRequests);
    }

    /**
     * Get vacation_requests
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getVacationRequests()
    {
        return $this->vacation_requests;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return User
     */
    public function setCreated($created)
    {
        $this->created = $created;
    
        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return User
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    
        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime 
     */
    public function getUpdated()
    {
        return $this->updated;
    }
}