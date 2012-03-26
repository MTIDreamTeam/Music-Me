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
    if (!Authentication::isAuthenticated($request))
      return $this->redirect($this->generateUrl('MTIMusicAndMeBundle_login'));
    $session = $this->get('session');
    
    $user = $this->getDoctrine()
    ->getRepository('MTIMusicAndMeBundle:User')
    ->find($session->get('user_id'));
    
    $userName = $user == null ? null : $user->getFirstname() . ' ' . $user->getLastname();
   
   if ($this->getRequest()->getMethod() === 'POST' && json_decode($this->getRequest()->getContent(), true) != null) {
		if (0 === strpos($this->getRequest()->headers->get('Content-Type'), 'application/json'))
		{
			$data = json_decode($this->getRequest()->getContent(), true);
			$toSearch = $data['searchZik'];
		}
		else
			$toSearch = $request->request->get('searchZik');

      $liste_zik = $this->getDoctrine()
      ->getEntityManager()
      ->getRepository('MTIMusicAndMeBundle:Musique')
      ->searchMusic($toSearch);
	if ($liste_zik == null)
	{
		if (0 === strpos($this->getRequest()->headers->get('Content-Type'), 'application/json'))
			return $this->render(
				'MTIMusicAndMeBundle:Search:viewSearchZiknull.ajax.twig',
				array(
				'is_connected' => $user == null ? false : true,
				'user_name' => $userName,
				'toSearch' => $toSearch
				)
			);
		else
			return $this->render(
				'MTIMusicAndMeBundle:Search:viewSearchZiknull.html.twig',
				array(
				'is_connected' => $user == null ? false : true,
				'user_name' => $userName,
				'toSearch' => $toSearch
				)
			);
	}
	else {
		if (0 === strpos($this->getRequest()->headers->get('Content-Type'), 'application/json'))
			return $this->render(
				'MTIMusicAndMeBundle:Search:resultSearchZik.ajax.twig',
				array(
				'is_connected' => $user == null ? false : true,
				'user_name' => $userName,
				'toSearch' => $toSearch,
				'listeZik' => $liste_zik
				)
			);
		else
			return $this->render(
				'MTIMusicAndMeBundle:Search:resultSearchZik.html.twig',
				array(
				'is_connected' => $user == null ? false : true,
				'user_name' => $userName,
				'toSearch' => $toSearch,
				'listeZik' => $liste_zik
				)
			);
	}
    } 
    else {
	    if (0 === strpos($this->getRequest()->headers->get('Content-Type'), 'application/json'))
		return $this->render(
			'MTIMusicAndMeBundle:Search:viewSearchZik.ajax.twig',
			array(
			'is_connected' => $user == null ? false : true,
			'user_name' => $userName,
			)
		);
	    else
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
