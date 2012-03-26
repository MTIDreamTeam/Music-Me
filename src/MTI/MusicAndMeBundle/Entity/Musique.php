<?php
namespace MTI\MusicAndMeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MTI\MusicAndMeBundle\Entity\MusiqueRepository")
 */
class Musique
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
     * @ORM\Column(name="genre", type="string", length=255)
     */
    public $genre;

     /**
     * @ORM\Column(name="year", type="integer", nullable=true)
     */
    public $year;

    /**
     * @ORM\Column(name="duree", type="integer", nullable=true)
     */
    public $duree;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public $path;

    /**
     * @Assert\File(maxSize="100000000000000")
     */ 
    public $file;

    /**
     * @ORM\ManyToOne(targetEntity="MTI\MusicAndMeBundle\Entity\Album")
     */
    public $album;

	/**
	 * @ORM\OneToMany(targetEntity="StreamRecords", mappedBy="user")
	 */
	protected $streamRecords;

    static public  function dureeToSec($str)
    {
      list($min, $sec) = explode(':', $str);
      $total = $min * 60 + $sec;
      return $total;
    }

    static public  function  secToDuree($sec)
    {
	$min = $sec / 60;
	$rest = $sec % $min;
	$duree = $min.":".$rest;
	return $duree;
    }
    
    public function getAbsolutePath()
    {
        return null === $this->path ? null : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getWebPath()
    {
        return null === $this->path ? null : $this->getUploadDir().'/'.$this->path;
    }

    public function getUploadRootDir()
    {
        // the absolute directory path where uploaded documents should be saved
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    public function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw when displaying uploaded doc/image in the view.
        return 'uploads/musique';
    }


    public function upload($art, $alb)
    {
      if (null === $this->file) {
	return;
      }
      
      $getid3 = new \getID3_getID3();
      $getid3->encoding = 'UTF-8';
      $info = $getid3->Analyze($this->file);
      
	if (isset($getid3->info['tags']['id3v2']))
	{
		if (isset($getid3->info['tags']['id3v2']['title']) &&
		isset($getid3->info['tags']['id3v2']['genre']) &&
		isset($getid3->info['tags']['id3v2']['year']) &&
		isset($getid3->info['tags']['id3v2']['album']) &&
		isset($getid3->info['tags']['id3v2']['artist']))
		{
			$this->title = $getid3->info['tags']['id3v2']['title'][0];
			$this->year = $getid3->info['tags']['id3v2']['year'][0];

			$test = $this->dureeToSec($getid3->info['playtime_string']);
			$this->duree = $this->dureeToSec($getid3->info['playtime_string']);
			$this->genre = $getid3->info['tags']['id3v2']['genre'][0];

			$this->album = $alb;

			$this->album->title = $getid3->info['tags']['id3v2']['album'][0];
			$this->album->artiste = $art;
			$this->album->artiste->name = $getid3->info['tags']['id3v2']['artist'][0];
		}
	}
	else if (isset($getid3->info['tags']['id3v1']))
	{
		if (isset($getid3->info['tags']['id3v1']['title']) &&
		isset($getid3->info['tags']['id3v1']['genre']) &&
		isset($getid3->info['tags']['id3v1']['year']) &&
		isset($getid3->info['tags']['id3v1']['album']) &&
		isset($getid3->info['tags']['id3v1']['artist']))
		{
			$this->title = $getid3->info['tags']['id3v1']['title'][0];
			$this->year = $getid3->info['tags']['id3v1']['year'][0];
			$this->duree = $getid3->info['playtime_string'];
			$this->genre = $getid3->info['tags']['id3v1']['genre'][0];

			$this->album = $alb;
			$this->album->title = $getid3->info['tags']['id3v1']['album'][0];
			$this->album->artiste = $art;
			$this->album->artiste->name = $getid3->info['tags']['id3v1']['artist'][0];
		}
	}
    }
    public function save()
    {
      // move takes the target directory and then the target filename to move to
      $this->file->move($this->getUploadRootDir()."/".$this->album->artiste->name."/".$this->album->title, $this->title.".mp3");
      // clean up the file property as you won't need it anymore
      $this->file = null;
    }

    public function  place()
    {
      // move takes the target directory and then the target filename to move to
      $this->file->move($this->getUploadRootDir()."/".$this->album->artiste->name."/".$this->album->title, $this->title.".mp3");
    // clean up the file property as you won't need it anymore
      $this->file = null;
    }

    public function destroy()
    {
      unlink($this->file->getPathname());
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
     * Set genre
     *
     * @param string $genre
     */
    public function setGenre($genre)
    {
        $this->genre = $genre;
    }

    /**
     * Get genre
     *
     * @return string
     */
    public function getGenre()
    {
        return $this->genre;
    }

    /**
     * Set year
     *
     * @param integer $year
     */
    public function setYear($year)
    {
        $this->year = $year;
    }

    /**
     * Get year
     *
     * @return integer
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set duree
     *
     * @param string $duree
     */
    public function setDuree($duree)
    {
        $this->duree = $duree;
    }

    /**
     * Get duree
     *
     * @return string
     */
    public function getDuree()
    {
        return $this->duree;
    }

    /**
     * Set path
     *
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }
    
    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
	    return $this->path;
    }

    /**
     * Set album
     *
     * @param MTI\MusicAndMeBundle\Entity\Album $album
     */
    public function setAlbum(\MTI\MusicAndMeBundle\Entity\Album $album)
    {
        $this->album = $album;
    }

    /**
     * Get album
     *
     * @return MTI\MusicAndMeBundle\Entity\Album
     */
    public function getAlbum()
    {
        return $this->album;
    }
    public function __construct()
    {
        $this->streamRecords = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add streamRecords
     *
     * @param MTI\MusicAndMeBundle\Entity\StreamRecords $streamRecords
     */
    public function addStreamRecords(\MTI\MusicAndMeBundle\Entity\StreamRecords $streamRecords)
    {
        $this->streamRecords[] = $streamRecords;
    }

    /**
     * Get streamRecords
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getStreamRecords()
    {
        return $this->streamRecords;
    }
}