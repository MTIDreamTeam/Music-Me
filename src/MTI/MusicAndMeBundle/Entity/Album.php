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
  
  
}