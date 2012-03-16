<?php
namespace MTI\MusicAndMeBundle\Controller;

use MTI\MusicAndMeBundle\Entity\Musique;
use MTI\MusicAndMeBundle\Entity\Artiste;
use MTI\MusicAndMeBundle\Entity\Album;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;

class UploadController extends Controller
{
    public function indexAction()
    {
      $zik = new Musique();
      $err = "";

      $formBuilder = $this->createFormBuilder($zik);
      $formBuilder->add('file');
      $form = $formBuilder->getForm();
      
      if ($this->getRequest()->getMethod() === 'POST') {
        $form->bindRequest($this->getRequest());
        if ($form->isValid()) {
	    if ($zik->file->getMimeType() == "application/zip")
	    {
	      $this->parseZip($zik->file, $zik->getUploadRootDir());
	    }
	    else if ($zik->file->getMimeType() == "audio/mp4"
	      || $zik->file->getMimeType() == "audio/mpeg") {
		$this->saveZik($zik, true);
	    }
          return $this->redirect($this->generateUrl('MTIMusicAndMeBundle_upload', array('form' => $form->createView(), 'err' => $err)));
        }
    }
      return $this->render('MTIMusicAndMeBundle:Upload:index.html.twig', array('form' => $form->createView()));
    }

    public function parseZip($file, $loc)
    {
      $zip = zip_open($file);
      if(is_resource($zip))
      {
	$tree = "";
	while(($zip_entry = zip_read($zip)) !== false)
	{
	  try
	  {
	  
	  echo "Unpacking ".zip_entry_name($zip_entry)."<br>";

	  
	  if(strpos(zip_entry_name($zip_entry), DIRECTORY_SEPARATOR) !== false)
	  {
	    $last = strrpos(zip_entry_name($zip_entry), DIRECTORY_SEPARATOR);
	    
	    $dir = substr(zip_entry_name($zip_entry), 0, $last);
	    
	    $dir = $loc."/".$dir;
	    
	    $file = substr(zip_entry_name($zip_entry), strrpos(zip_entry_name($zip_entry), DIRECTORY_SEPARATOR)+1);
	    /*if(!is_dir($dir))
	    {
	      mkdir($dir, 0755, true) or die("Unable to create $dir\n");
	    }*/
	    if(strlen(trim($file)) > 0)
	    {
	      if(substr(zip_entry_name($zip_entry), -3, 3) == "mp3")
	      {
		$zik = new Musique();
 		$return = file_put_contents($loc."/".$file, zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)));
		$zik->file = new File($loc."/".$file);
		$zik->path = $file;
		
		$this->saveZik($zik, false);
		if ($zik->title == null)
		  $zik->destroy();
		if($return === false)
		{
		  die("Unable to write file $loc/$file\n");
		}
	      }
	    }
	  }
	  else
	  {
	    $zik = new Musique();
	    if(substr(zip_entry_name($zip_entry), -3, 3) == "mp3")
	    {
	      file_put_contents($file, zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)));
	      $zik->file = new File($loc."/".$file);
	      $zik->path = $file;
	      $this->saveZik($zik, false);
	      if ($zik->title == null)
		$zik->destroy();
	    }
	  }
	}
	catch(ErrorException $e) {
	  echo "Un problem a eu lieu pour ".zip_entry_name($zip_entry)."<br>";
	}
	
	}
      }
      else
      {
	echo "Unable to open zip file\n";
      }
    }
    
    public function saveZik($zik, $save)
    {
      $em = $this->getDoctrine()->getEntityManager();
      $art = new Artiste;
      $alb = new Album;
      $zik->upload($art, $alb);
      if ($zik->title == null)
	return;
      $zik->path = $art->name."/".$alb->title."/".$zik->title.".mp3";
      
      $repository = $this->getDoctrine()
      ->getEntityManager()
      ->getRepository('MTIMusicAndMeBundle:Artiste');
      $artiste = $repository->findOneBy(array('name' => $art->name));
      
      if (!$artiste) {
	mkdir($zik->getUploadRootDir()."/".$art->name, 0755, true) or die("Unable to create\n");
	echo "create artiste<br>";
	$em->persist($art);
      }
      else
      {
	$art = $artiste;
      }
      $repository = $this->getDoctrine()
      ->getEntityManager()
      ->getRepository('MTIMusicAndMeBundle:Album');
      $album = $repository->findOneBy(array('title' => $alb->title));
      
      if (!$album) {
	$alb->artiste = $art;
	mkdir($zik->getUploadRootDir()."/".$art->name."/".$alb->title, 0755, true) or die("Unable to create\n");
	echo "create album<br>";
	$em->persist($alb);
      }
      else {
	$alb = $album;
      }
      
      
      $repository = $this->getDoctrine()
      ->getEntityManager()
      ->getRepository('MTIMusicAndMeBundle:Musique');
      $musique = $repository->findOneBy(array('title' => $zik->title, 'year' => $zik->year));
      
      if (!$musique) {
	$zik->album = $alb;
	$em->persist($zik);
	if ($save)
	  $zik->save();
	else {
	    $zik->place();
	}
	
      }
      else {
	if (!$save)
	  $zik->destroy();
      }
      
      $em->flush();
    }
}
