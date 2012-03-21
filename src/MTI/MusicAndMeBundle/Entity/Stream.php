<?php

namespace MTI\MusicAndMeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;

use MTI\MusicAndMeBundle\Entity\User;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="stream")
 */
class Stream
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
    protected $name;
	
    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

	/**
	 * @ORM\ManyToOne(targetEntity="User", inversedBy="streams")
	 * @ORM\JoinColumn(name="owner_id", referencedColumnName="id")
	 */
    protected $owner;
	
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
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set owner
     *
     * @param MTI\MusicAndMeBundle\Entity\User $owner
     */
    public function setOwner(\MTI\MusicAndMeBundle\Entity\User $owner)
    {
        $this->owner = $owner;
    }

    /**
     * Get owner
     *
     * @return MTI\MusicAndMeBundle\Entity\User 
     */
    public function getOwner()
    {
        return $this->owner;
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
}