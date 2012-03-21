<?php
namespace MTI\MusicAndMeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;



/**
 * @ORM\Entity
 */
class Album
{
  /**
   * @ORM\Id
   * @ORM\Column(name="id", type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  public $id;
  
  /**
   * @ORM\Column(name="title", type="string", length=255)
   */
  public $title;
  
  /**
   * @ORM\ManyToOne(targetEntity="MTI\MusicAndMeBundle\Entity\Artiste")
   */
  public $artiste;
  
  

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
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set artiste
     *
     * @param MTI\MusicAndMeBundle\Entity\Artiste $artiste
     */
    public function setArtiste(\MTI\MusicAndMeBundle\Entity\Artiste $artiste)
    {
        $this->artiste = $artiste;
    }

    /**
     * Get artiste
     *
     * @return MTI\MusicAndMeBundle\Entity\Artiste 
     */
    public function getArtiste()
    {
        return $this->artiste;
    }
}