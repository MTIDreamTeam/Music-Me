<?php
namespace MTI\MusicAndMeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;



/**
 * @ORM\Entity
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    public $name;

    /**
     * @ORM\Column(name="title", type="string", length=255)
     */
    public $title;

     /**
     * @ORM\Column(name="year", type="integer", nullable=true)
     */
    public $year;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public $path;

    /**
     * @Assert\File(maxSize="1000000000")
     */
    public $file;

    public function getAbsolutePath()
    {
        return null === $this->path ? null : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getWebPath()
    {
        return null === $this->path ? null : $this->getUploadDir().'/'.$this->path;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded documents should be saved
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw when displaying uploaded doc/image in the view.
        return 'uploads/musique';
    }

    public function upload()
    {
      // the file property can be empty if the field is not required
      if (null === $this->file) {
	return;
      }
      echo "id -> ".$this->id;
      echo "extension -> ".$this->file->guessExtension();
      echo "name -> ".$this->file->getClientOriginalName();
      // we use the original file name here but you should
      // sanitize it at least to avoid any security issues
      
      $getid3 = new \getID3_getID3();
      $getid3->encoding = 'UTF-8';
      try {
	
	$info = $getid3->Analyze($this->file);
	echo '<br>Durée :  <strong>' . $info['playtime_string'] . '</strong><br>';
	foreach ($getid3->info['tags'] as $tag => $tag_info)
	  foreach($getid3->info['tags'][$tag] as $t => $i)
	   echo $tag."#".$i[0]."|".$t."<br>";
	   
      }
      catch (Exception $e) {
	
	echo 'An error occured: ' .  $e->message;
      }
	
        echo '<br>Artiste :  <strong>' . $getid3->info['tags']['id3v2']['artist'][0] . '</strong><br>';
	echo 'Album :  <strong>' . $getid3->info['tags']['id3v2']['album'][0] . '</strong><br>';
	echo 'Genre :  <strong>' . $getid3->info['tags']['id3v2']['genre'][0] . '</strong><br>';
	echo 'Annee de parution :  <strong>' . $getid3->info['tags']['id3v2']['year'][0] . '</strong><br>';
	

      // move takes the target directory and then the target filename to move to
      $this->file->move($this->getUploadRootDir(), $this->file->getClientOriginalName());
      // set the path property to the filename where you'ved saved the file
      $this->path = $this->file->getClientOriginalName();
      echo "path : ".$this->path;
      // clean up the file property as you won't need it anymore
      $this->file = null;
    }
}