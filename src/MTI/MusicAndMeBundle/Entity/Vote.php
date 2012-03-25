<?php

namespace MTI\MusicAndMeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;

use MTI\MusicAndMeBundle\Entity\User;
use MTI\MusicAndMeBundle\Entity\Stream;
use MTI\MusicAndMeBundle\Entity\StreamRecords;
use MTI\MusicAndMeBundle\Entity\Musique;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="vote")
 */
class Vote
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
	
    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

	/**
	 * @ORM\ManyToOne(targetEntity="User", inversedBy="votes")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
	 */
    protected $user;
	
	/**
	 * @ORM\ManyToOne(targetEntity="StreamRecords", inversedBy="votes")
	 * @ORM\JoinColumn(name="stream_record_id", referencedColumnName="id")
	 */
    protected $streamRecord;
	
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

    /**
     * Set music
     *
     * @param MTI\MusicAndMeBundle\Entity\Musique $music
     */
    public function setMusic(\MTI\MusicAndMeBundle\Entity\Musique $music)
    {
        $this->music = $music;
    }

    /**
     * Get music
     *
     * @return MTI\MusicAndMeBundle\Entity\Musique 
     */
    public function getMusic()
    {
        return $this->music;
    }

    /**
     * Set streamRecord
     *
     * @param MTI\MusicAndMeBundle\Entity\StreamRecord $streamRecord
     */
    public function setStreamRecord(\MTI\MusicAndMeBundle\Entity\StreamRecord $streamRecord)
    {
        $this->streamRecord = $streamRecord;
    }

    /**
     * Get streamRecord
     *
     * @return MTI\MusicAndMeBundle\Entity\StreamRecord 
     */
    public function getStreamRecord()
    {
        return $this->streamRecord;
    }
}