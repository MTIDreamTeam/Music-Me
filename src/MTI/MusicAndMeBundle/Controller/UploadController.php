<?php
namespace MTI\MusicAndMeBundle\Controller;

use MTI\MusicAndMeBundle\Entity\Musique;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class UploadController extends Controller
{
    public function indexAction()
    {
      $zik = new Musique();

      $formBuilder = $this->createFormBuilder($zik);
      $formBuilder->add('file');
      $form = $formBuilder->getForm();
      
      if ($this->getRequest()->getMethod() === 'POST') {
        $form->bindRequest($this->getRequest());
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();

	    $zik->upload();
            //$em->persist($zik);
            //$em->flush();

            //$this->redirect($this->generateUrl('upload'));
            return;
        }
    }
      
      return $this->render('MTIMusicAndMeBundle:Upload:index.html.twig', array('form' => $form->createView()));
    }

    public function saveAction()
    {
	/*if( $request->getMethod() == 'POST' )
	{
	  $post = $request->request;
	  $file = $post->get('zik');
	}*/
    }
}
