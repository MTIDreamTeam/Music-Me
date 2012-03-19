<?php

namespace MTI\MusicAndMeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\SessionStorage\PdoSessionStorage;

use MTI\MusicAndMeBundle\Entity\User;
use MTI\MusicAndMeBundle\Entity\LoginUser;
use MTI\MusicAndMeBundle\Security\Authentication;


class StreamController extends Controller
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
		
		return $this->render(
			'MTIMusicAndMeBundle:Stream:index.html.twig',
			array(
				'is_connected' => $user == null ? false : true,
				'user_name' => $userName,
			)
		);
	}
}
