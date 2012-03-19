<?php

namespace MTI\MusicAndMeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\SessionStorage\PdoSessionStorage;

use MTI\MusicAndMeBundle\Entity\User;


class HomeController extends Controller
{
    
    public function indexAction(Request $request)
    {
		$session = $this->get('session');
		
		$user = $this->getDoctrine()
					 ->getRepository('MTIMusicAndMeBundle:User')
					 ->find($session->get('user_id'));
		
		$userName = $user == null ? null : $user->getFirstname() . ' ' . $user->getLastname();
		
		if ($userName)
		{
			$streams = $this->getDoctrine()
							->getRepository('MTIMusicAndMeBundle:Stream')
							->findBy(array('owner' => $user->getId()));
			
	        return $this->render('MTIMusicAndMeBundle:Home:indexLoggedIn.html.twig', array(
				'is_connected' => $user == null ? false : true,
	        	'user_name' => $userName,
				'my_streams' => $streams,
	        ));
		}
		else
		{
	        return $this->render('MTIMusicAndMeBundle:Home:index.html.twig', array(
				'is_connected' => $user == null ? false : true,
	        	'user_name' => $userName,
	        ));
		}
    }
}
