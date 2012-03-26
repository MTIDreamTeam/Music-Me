<?php

namespace MTI\MusicAndMeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;

use MTI\MusicAndMeBundle\Entity\User;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="played_stream")
 */
class PlayedStream
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="User", inversedBy="playedStreams")
	 * @ORM\JoinColumn(name="user", referencedColumnName="id")
	 */
    protected $user;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Stream", inversedBy="playedStreams")
	 * @ORM\JoinColumn(name="stream", referencedColumnName="id")
	 */
    protected $stream;
	
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
     * Set user
     *
     * @param MTI\MusicAndMeBundle\Entity\User $user
     */
    public function setUser(\MTI\MusicAndMeBundle\Entity\User $user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return MTI\MusicAndMeBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set stream
     *
     * @param MTI\MusicAndMeBundle\Entity\Stream $stream
     */
    public function setStream(\MTI\MusicAndMeBundle\Entity\Stream $stream)
    {
        $this->stream = $stream;
    }

    /**
     * Get stream
     *
     * @return MTI\MusicAndMeBundle\Entity\Stream 
     */
    public function getStream()
    {
        return $this->stream;
    }
}