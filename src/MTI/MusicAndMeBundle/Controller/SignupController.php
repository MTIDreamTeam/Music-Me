<?php

namespace MTI\MusicAndMeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use MTI\MusicAndMeBundle\Entity\User;


class SignupController extends Controller
{
	public function indexAction(Request $request)
	{
		$user = new User();

		$form = $this->createFormBuilder($user)->add('firstname', 'text')
												->add('lastname', 'text')
												->add('email', 'email')
												->add('password', 'password')
												->getForm();

		return $this->render(
			'MTIMusicAndMeBundle:Signup:index.html.twig',
			array(
				'form' => $form->createView()
			)
		);
	}
}
