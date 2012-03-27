<?php
namespace MTI\MusicAndMeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\DependencyInjection\SimpleXMLElement;


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
   * @ORM\Column(type="string", length=255, nullable=true)
   */
  public $coverPath;
  
  public function getCoverRootDir()
  {
	  // the absolute directory path where uploaded documents should be saved
	  return __DIR__.'/../../../../web/'.$this->getCoverDir();
  }

  public function getCover()
  {
      $xml_request_url = 'http://ws.audioscrobbler.com/2.0/?method=album.getinfo&api_key=b25b959554ed76058ac220b7b2e0a026&artist='
      .urlencode($this->artiste->name).'&album='.urlencode($this->title);
      $xml = new SimpleXMLElement($xml_request_url, null, true);
      if (!isset($xml) || $xml == null || $xml == "")
	      return "n";
      if ($xml->getName() == "lfm")
      {
	foreach($xml->attributes() as $a => $b) {
	  if ($b != "ok")
	    return;
	}
	$children = $xml->children()->children();
	return $children->image[3];
      }
  }
    
  public function getCoverDir()
  {
	  // get rid of the __DIR__ so it doesn't screw when displaying uploaded doc/image in the view.
	  return '/uploads/musique/covers';
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

    /**
     * Get coverPath
     *
     * @return string
     */
    public function getCoverPath()
    {
	    return $this->coverPath;
    }
    
    /**
     * Set coverPath
     *
     * @param string $coverPath
     */
    public function setCoverPath($coverPath)
    {
	    $this->coverPath = $coverPath;
    }
}