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
	public function getHeaderAction(Request $request)
	{
		if (!Authentication::isAuthenticated($request))
			return $this->redirect($this->generateUrl('MTIMusicAndMeBundle_login'));
		
		$session = $this->get('session');
		
		$user = $this->getDoctrine()
		->getRepository('MTIMusicAndMeBundle:User')
		->find($session->get('user_id'));
		
		$userName = $user == null ? null : $user->getFirstname() . ' ' . $user->getLastname();
		return $this->render(
		'MTIMusicAndMeBundle:Header:header_right.html.twig',
				array(
					'is_connected' => $user == null ? false : true,
					'user_name' => $userName,
				)
			);	
	}
	
	public function indexAction(Request $request)
	{
		if (!Authentication::isAuthenticated($request))
			return $this->redirect($this->generateUrl('MTIMusicAndMeBundle_login'));

		$session = $this->get('session');

		
		$user = $this->getDoctrine()
						->getRepository('MTIMusicAndMeBundle:User')
						->find($session->get('user_id'));

		$userName = $user == null ? null : $user->getFirstname() . ' ' . $user->getLastname();
		
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
					'MTIMusicAndMeBundle:Account:index.html.twig',
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

				return $this->redirect($this->generateUrl('MTIMusicAndMeBundle_homepage'));
			}
		}
		else
		{
			return $this->render(
				'MTIMusicAndMeBundle:Account:index.html.twig',
				array(
					'form' => $form->createView(),
					'user_name' => $userName,
					'is_connected' => $user == null ? false : true,
				)
			);
		}
		
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
				if (0 === strpos($this->getRequest()->headers->get('Content-Type'), 'application/json'))
					return $this->render(
						'MTIMusicAndMeBundle:Account:create.ajax.twig',
						array(
							'form' => $form->createView()
						)
					);
				else
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
			if (0 === strpos($this->getRequest()->headers->get('Content-Type'), 'application/json'))
				return $this->render(
					'MTIMusicAndMeBundle:Account:create.ajax.twig',
					array(
						'form' => $form->createView(),
						'user_name' => null,
						'is_connected' => null,
					)
				);
			else
				return $this->render(
					'MTIMusicAndMeBundle:Account:create.html.twig',
					array(
						'form' => $form->createView(),
						'user_name' => null,
						'is_connected' => null,
					)
				);



		}
	}

	public function loginAction(Request $request)
	{
		// return new Response($this->get('session')->get('nextRoute') . ' ' . $request->attributes->get('_route'));
		if (Authentication::isAuthenticated($request))
		{
			// return new Response($this->get('session')->get('nextRoute'));
			return $this->redirect($this->generateUrl($this->get('session')->get('nextRoute')));
		}
		// return new Response($this->get('session')->get('nextRoute') . ' ' . $request->attributes->get('_route'));

		$user = new LoginUser();

		$form = $this->createFormBuilder($user)->add('email', 'email')
												->add('password', 'password')
												->getForm();
		$userName = null;
		if ($request->getMethod() == 'POST')
		{
			$form->bindRequest($request);

			$validator = $this->get('validator');
			$errors = $validator->validate($user);

			// Found some errors in the login form
			if (count($errors) > 0)
			{
				if (0 === strpos($this->getRequest()->headers->get('Content-Type'), 'application/json'))
					return $this->render(
						'MTIMusicAndMeBundle:Account:login.ajax.twig',
						array(
							'form' => $form->createView()
						)
					);
				else
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
					if (0 === strpos($this->getRequest()->headers->get('Content-Type'), 'application/json'))
						return $this->render(
							'MTIMusicAndMeBundle:Account:login.ajax.twig',
							array(
								'form' => $form->createView(),
								'login_error' => 'Email incorrect'
							)
						);
					else
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
						if (0 === strpos($this->getRequest()->headers->get('Content-Type'), 'application/json'))
							return $this->render(
								'MTIMusicAndMeBundle:Account:login.ajax.twig',
								array(
									'form' => $form->createView(),
									'login_error' => 'Mot de passe incorrect'
								)
							);
						else
							return $this->render(
								'MTIMusicAndMeBundle:Account:login.html.twig',
								array(
									'form' => $form->createView(),
									'login_error' => 'Mot de passe incorrect'
								)
							);
					}
				}

				$route = $this->get('session')->get('nextRoute');
				// In case the user goes directly to the login page
				if ($route == '')
					$route = 'MTIMusicAndMeBundle_homepage';

				$this->get('session')->set('user_id', $registeredUser->getId());
				// return new Response($route);

				return $this->redirect($this->generateUrl($route));
			}
		}
		else
		{
			if (0 === strpos($this->getRequest()->headers->get('Content-Type'), 'application/json'))
				return $this->render(
					'MTIMusicAndMeBundle:Account:login.ajax.twig',
					array(
						'form' => $form->createView(),
						'is_connected' => null,
						'user_name' => $userName,
					)
				);
			else
				return $this->render(
					'MTIMusicAndMeBundle:Account:login.html.twig',
					array(
						'form' => $form->createView(),
						'is_connected' => null,
						'user_name' => $userName,
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
