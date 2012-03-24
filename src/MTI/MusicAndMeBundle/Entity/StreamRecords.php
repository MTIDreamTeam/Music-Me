<?php

namespace MTI\MusicAndMeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;

use MTI\MusicAndMeBundle\Entity\User;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="stream_records")
 */
class StreamRecords
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
    protected $played;

	/**
	 * @ORM\ManyToOne(targetEntity="Stream", inversedBy="streamRecords")
	 * @ORM\JoinColumn(name="stream_id", referencedColumnName="id")
	 */
    protected $stream;

	/**
	 * @ORM\ManyToOne(targetEntity="Musique", inversedBy="streamRecords")
	 * @ORM\JoinColumn(name="music_id", referencedColumnName="id")
	 */
    protected $music;

	/**
	 * @ORM\OneToMany(targetEntity="Vote", mappedBy="streamRecord")
	 */
	protected $votes;

	/**
	 * @ORM\PrePersist
	 */
	public function onPrePersist()
	{
	    $this->played = new \DateTime();
	}

	public function isPlaying()
	{
		$now = new \DateTime();
		return ($this->getPlayed()->getTimestamp() + $this->getMusic()->getDuree() > $now->getTimestamp()) ? true : false;
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

    /**
     * Set played
     *
     * @param datetime $played
     */
    public function setPlayed($played)
    {
        $this->played = $played;
    }

    /**
     * Get played
     *
     * @return datetime 
     */
    public function getPlayed()
    {
        return $this->played;
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
    public function __construct()
    {
        $this->votes = new \Doctrine\Common\Collections\ArrayCollection();
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