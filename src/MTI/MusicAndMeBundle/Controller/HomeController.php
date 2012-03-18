<?php

namespace MTI\MusicAndMeBundle\Controller;

use MTI\MusicAndMeBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class HomeController extends Controller
{
    
    public function indexAction()
    {
		$session = $this->get('session');
		
		$user = $this->getDoctrine()
						->getRepository('MTIMusicAndMeBundle:User')
						->find($session->get('user_id'));
		
		$userName = $user == null ? null : $user->getFirstname() . ' ' . $user->getLastname();
		
        return $this->render('MTIMusicAndMeBundle:Home:index.html.twig', array(
			'is_connected' => $user == null ? false : true,
        	'user_name' => $userName,
        ));
    }
}
