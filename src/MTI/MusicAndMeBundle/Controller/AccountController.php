<?php

namespace MTI\MusicAndMeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\SessionStorage\PdoSessionStorage;

use MTI\MusicAndMeBundle\Entity\User;
use MTI\MusicAndMeBundle\Entity\LoginUser;
use MTI\MusicAndMeBundle\Security\Authentication;


class AccountController extends Controller
{
	public function indexAction(Request $request)
	{
		if (!Authentication::isAuthenticated($request))
			return $this->redirect($this->generateUrl('MTIMusicAndMeBundle_login'), 301);
		
		$session = $this->get('session');
		
		$user = $this->getDoctrine()
								->getRepository('MTIMusicAndMeBundle:User')
								->find($session->get('user_id'));
		
		return new Response("Hello ".$user->getFirstname() . ' ' . $user->getLastname());
	}
	
	public function createAction(Request $request)
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
				$user->setPassword(md5($user->getEmail() . $user->getPassword()));
				
				$em = $this->getDoctrine()->getEntityManager();
				$em->persist($user);
				$em->flush();
				
				return $this->redirect($this->generateUrl('MTIMusicAndMeBundle_account'), 302);
			}
		}
		else
		{
			return $this->render(
				'MTIMusicAndMeBundle:Account:create.html.twig',
				array(
					'form' => $form->createView()
				)
			);
		}
	}
	
	public function loginAction(Request $request)
	{
		if (Authentication::isAuthenticated($request))
			return $this->redirect($this->generateUrl('MTIMusicAndMeBundle_account'), 301);
		
		$user = new LoginUser();

		$form = $this->createFormBuilder($user)->add('email', 'email')
												->add('password', 'password')
												->getForm();
		
		if ($request->getMethod() == 'POST')
		{
			$form->bindRequest($request);
			
			$validator = $this->get('validator');
			$errors = $validator->validate($user);

			// Found some errors in the login form
			if (count($errors) > 0)
			{
				return $this->render(
					'MTIMusicAndMeBundle:Account:login.html.twig',
					array(
						'form' => $form->createView()
					)
				);
			}
			// There is no semantical errors in the form
			else
			{
				$results = $this->getDoctrine()
								->getRepository('MTIMusicAndMeBundle:User')
								->findBy(array('email' => $form->getData()->getEmail()));
				
				if (count($results) == 0)
				{
					return $this->render(
						'MTIMusicAndMeBundle:Account:login.html.twig',
						array(
							'form' => $form->createView(),
							'login_error' => 'Email incorrect'
						)
					);
				}
				else
				{
					$registeredUser = $results[0];
					$hashedFormPassword = md5($form->getData()->getEmail() . $form->getData()->getPassword());
					if ($registeredUser->getPassword() != $hashedFormPassword)
					{
						return $this->render(
							'MTIMusicAndMeBundle:Account:login.html.twig',
							array(
								'form' => $form->createView(),
								'login_error' => 'Mot de passe incorrect'
							)
						);
					}
				}
				
				$session = $this->get('session');
				$route = $session->get('nextRoute');
				$session->set('nextRoute', 'MTIMusicAndMeBundle_account');
				$session->set('user_id', $registeredUser->getId());
				// return new Response('route : ');
				
				return $this->redirect($this->generateUrl($route), 301);
			}
		}
		else
		{
			return $this->render(
				'MTIMusicAndMeBundle:Account:login.html.twig',
				array(
					'form' => $form->createView()
				)
			);
		}
	}
	
	public function logoutAction(Request $request)
	{
		// Log the user out
		$session = $this->get('session');
		$session->invalidate();
		$this->get("security.context")->setToken(null);
		
		return new Response("session invalidated : ".$session->get('user_id'));
		
		return new Response($request->attributes->get('_route'));
		
		if ($session)
		{
			return $this->redirect($this->generateUrl('MTIMusicAndMeBundle_login'), 301);
		}
		
		return new Response("Failed to logout");
	}
}
