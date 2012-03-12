<?php

namespace MTI\MusicAndMeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use MTI\MusicAndMeBundle\Entity\User;
use Symfony\Component\Yaml\Yaml;


class AccountController extends Controller
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
			'MTIMusicAndMeBundle:Account:create.html.twig',
			array(
				'form' => $form->createView()
			)
		);
	}
	
	public function createAction(Request $request)
	{
		$user = new User();
		$form = $this->createFormBuilder($user)->add('firstname', 'text')
												->add('lastname', 'text')
												->add('email', 'email')
												->add('password', 'password')
												->getForm();

		return $this->render(
			'MTIMusicAndMeBundle:Account:create.html.twig',
			array(
				'form' => $form->createView()
			)
		);
	}
	
	public function validateAction(Request $request)
	{
		$user = new User();
		$form = $this->createFormBuilder($user)->add('firstname', 'text')
												->add('lastname', 'text')
												->add('email', 'email')
												->add('password', 'password')
												->getForm();

		if ($request->getMethod() == 'POST')
		{
			$form->bindRequest($request);
			
			$validator = $this->get('validator');
			$errors = $validator->validate($user);
		
			if (count($errors) > 0)
			{
				return $this->render(
					'MTIMusicAndMeBundle:Account:create.html.twig',
					array(
						'form' => $form->createView()
					)
				);
			}
			else
			{
				$em = $this->getDoctrine()->getEntityManager();
				$em->persist($user);
				$em->flush();
				
				return $this->redirect($this->generateUrl('MTIMusicAndMeBundle_create'), 302);
			}
		}
		else
		{
			return $this->redirect($this->generateUrl('MTIMusicAndMeBundle_create'), 302);
		}
	}
	
	public function loginAction(Request $request)
	{
		$user = new User();

		$form = $this->createFormBuilder($user)->add('firstname', 'text')
												->add('lastname', 'text')
												->add('email', 'email')
												->add('password', 'password')
												->getForm();

		return $this->render(
			'MTIMusicAndMeBundle:Account:create.html.twig',
			array(
				'form' => $form->createView()
			)
		);
	}
}
