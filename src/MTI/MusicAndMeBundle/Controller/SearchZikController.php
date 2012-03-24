<?php

namespace MTI\MusicAndMeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\SessionStorage\PdoSessionStorage;


use MTI\MusicAndMeBundle\Entity\Stream;
use MTI\MusicAndMeBundle\Entity\User;
use MTI\MusicAndMeBundle\Entity\LoginUser;
use MTI\MusicAndMeBundle\Security\Authentication;


class SearchZikController extends Controller
{

  public function indexAction(Request $request)
  {
	$streamId = $request->query->get('stream_id');
    if (!Authentication::isAuthenticated($request))
      return $this->redirect($this->generateUrl('MTIMusicAndMeBundle_login'));

    $session = $this->get('session');

    $user = $this->getDoctrine()
    ->getRepository('MTIMusicAndMeBundle:User')
    ->find($session->get('user_id'));

    $stream = $this->getDoctrine()
    ->getRepository('MTIMusicAndMeBundle:Stream')
    ->find($streamId);
    if ($stream == null)
	    return $this->redirect($this->generateUrl('MTIMusicAndMeBundle_homepage'));
    $userName = $user == null ? null : $user->getFirstname() . ' ' . $user->getLastname();

    if ($this->getRequest()->getMethod() === 'POST') {
      $toSearch = $request->request->get('searchZik');


      $liste_zik = $this->getDoctrine()
      ->getEntityManager()
      ->getRepository('MTIMusicAndMeBundle:Musique')
      ->searchMusic($toSearch);
      
      if ($liste_zik == null)
      {
	return $this->render(
	  'MTIMusicAndMeBundle:Search:resultSearchZik.html.twig',
	  array(
		'is_connected' => $user == null ? false : true,
		'user_name' => $userName,
		'toSearch' => $toSearch,
		'stream' => $stream,
		'listeZik' => $liste_zik
	  )
	  );
      }
      else {
	return $this->render(
	  'MTIMusicAndMeBundle:Search:resultSearchZik.html.twig',
	  array(
	    'is_connected' => $user == null ? false : true,
	    'user_name' => $userName,
	    'toSearch' => $toSearch,
	    'listeZik' => $liste_zik,
	    'stream' => $stream
	)
	);
      }

    }
    else {
    return $this->render(
      'MTIMusicAndMeBundle:Search:viewSearchZik.html.twig',
      array(
	'is_connected' => $user == null ? false : true,
	'user_name' => $userName,
	)
	);
    }
  }
}