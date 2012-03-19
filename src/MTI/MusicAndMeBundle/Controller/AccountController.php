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
			return $this->redirect($this->generateUrl('MTIMusicAndMeBundle_login'));
		
		$session = $this->get('session');
		
		$user = $this->getDoctrine()
						->getRepository('MTIMusicAndMeBundle:User')
						->find($session->get('user_id'));
		
		$userName = $user == null ? null : $user->getFirstname() . ' ' . $user->getLastname();
		
		return $this->render(
			'MTIMusicAndMeBundle:Account:index.html.twig',
			array(
				'is_connected' => $user == null ? false : true,
				'user_name' => $userName,
			)
		);
	}
	
	public function createAction(Request $request)
	{
		if (Authentication::isAuthenticated($request))
			return $this->redirect($this->generateUrl('MTIMusicAndMeBundle_account'));
		
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
				
				return $this->redirect($this->generateUrl('MTIMusicAndMeBundle_account'));
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
		$session = $this->get('session');
		
		if (Authentication::isAuthenticated($request))
			return $this->redirect($this->generateUrl($session->get('nextRoute')));
		
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
				
				// Copies the requested in a new variable
				$route = $session->get('nextRoute');
				// return new Response($route);
				$session->set('nextRoute', 'MTIMusicAndMeBundle_account');
				$session->set('user_id', $registeredUser->getId());
				
				return $this->redirect($this->generateUrl($route));
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
		$session->set('user_id', null);
		$session->invalidate();
		$this->get("security.context")->setToken(null);
		
		return $this->redirect($this->generateUrl('MTIMusicAndMeBundle_homepage'));
	}
}
