<?php

namespace MTI\MusicAndMeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;

use MTI\MusicAndMeBundle\Entity\Stream;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="user")
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $firstname;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $lastname;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $email;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $password;

	/**
	 * @ORM\OneToMany(targetEntity="Stream", mappedBy="user")
	 */
	protected $streams;

	/**
	 * @ORM\OneToMany(targetEntity="Vote", mappedBy="user")
	 */
	protected $votes;
	
    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

	/**
	 * @ORM\PrePersist
	 */
	public function onPrePersist()
	{
	    $this->created = new \DateTime();
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
     * Set firstname
     *
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * Get firstname
     *
     * @return string 
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * Get lastname
     *
     * @return string 
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }
	
    public function __construct()
    {
        $this->streams = new ArrayCollection();
    }
    
    /**
     * Add streams
     *
     * @param MTI\MusicAndMeBundle\Entity\Stream $streams
     */
    public function addStream(\MTI\MusicAndMeBundle\Entity\Stream $streams)
    {
        $this->streams[] = $streams;
    }

    /**
     * Get streams
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getStreams()
    {
        return $this->streams;
    }

    /**
     * Set created
     *
     * @param datetime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * Get created
     *
     * @return datetime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Add votes
     *
     * @param MTI\MusicAndMeBundle\Entity\Vote $votes
     */
    public function addVote(\MTI\MusicAndMeBundle\Entity\Vote $votes)
    {
        $this->votes[] = $votes;
    }

    /**
     * Get votes
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getVotes()
    {
        return $this->votes;
    }
}