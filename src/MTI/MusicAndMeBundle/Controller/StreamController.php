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
		
		$myStreams = $this->getDoctrine()
						  ->getRepository('MTIMusicAndMeBundle:Stream')
						  ->findBy(array('owner' => $user->getId()));
		
		return $this->render(
			'MTIMusicAndMeBundle:Stream:index.html.twig',
			array(
				'is_connected' => $user == null ? false : true,
				'user_name' => $userName,
				'my_streams' => $myStreams,
			)
		);
	}
	
	public function createAction(Request $request)
	{
		// return new Response(var_dump(Authentication::isAuthenticated($request)));
		if (!Authentication::isAuthenticated($request))
			return $this->redirect($this->generateUrl('MTIMusicAndMeBundle_login'));
		
		$session = $this->get('session');
		
		$stream = new Stream();
		$form = $this->createFormBuilder($stream)->add('name', 'text')
												 ->getForm();
		
		$user = $this->getDoctrine()
					 ->getRepository('MTIMusicAndMeBundle:User')
					 ->find($session->get('user_id'));
		$userName = $user == null ? null : $user->getFirstname() . ' ' . $user->getLastname();
		
		if ($request->getMethod() == 'POST')
		{
			$form->bindRequest($request);
			
			$validator = $this->get('validator');
			$errors = $validator->validate($stream);
		
			if (count($errors) > 0)
			{
				return $this->render(
					'MTIMusicAndMeBundle:Stream:create.html.twig',
					array(
						'form' => $form->createView()
					)
				);
			}
			else
			{
				$results = $this->getDoctrine()
								->getRepository('MTIMusicAndMeBundle:Stream')
								->findBy(array('name' => $form->getData()->getName()));
				
				if (count($results) == 0)
				{
					$stream->setName($form->getData()->getName());
					$stream->setOwner($user);
					
					$em = $this->getDoctrine()->getEntityManager();
					$em->persist($stream);
					$em->flush();
					
					return $this->render(
						'MTIMusicAndMeBundle:Stream:index.html.twig',
						array(
							'is_connected' => $user == null ? false : true,
							'user_name' => $userName,
						)
					);
				}
				else
				{
					return $this->render(
						'MTIMusicAndMeBundle:Stream:create.html.twig',
						array(
							'form' => $form->createView(),
							'create_stream_error' => 'Un flux "' . $form->getData()->getName() . '" existe déjà',
						)
					);
				}
				
				$em = $this->getDoctrine()->getEntityManager();
				$em->persist($user);
				$em->flush();
				
				return $this->redirect($this->generateUrl('MTIMusicAndMeBundle_account'));
			}
			
			return $this->render(
				'MTIMusicAndMeBundle:Stream:create.html.twig',
				array(
					'is_connected' => $user == null ? false : true,
					'user_name' => $userName,
					'form' => $form->createView(),
				)
			);
		}
		else
		{
			return $this->render(
				'MTIMusicAndMeBundle:Stream:create.html.twig',
				array(
					'is_connected' => $user == null ? false : true,
					'user_name' => $userName,
					'form' => $form->createView(),
				)
			);
		}
	}
	
	public function viewAction(Request $request)
	{
		$streamId = $request->attributes->get('stream_id');
		
		if (!Authentication::isAuthenticated($request))
			return $this->redirect($this->generateUrl('MTIMusicAndMeBundle_login'));
		
		$session = $this->get('session');
		
		$user = $this->getDoctrine()
						->getRepository('MTIMusicAndMeBundle:User')
						->find($session->get('user_id'));
		
		$userName = $user == null ? null : $user->getFirstname() . ' ' . $user->getLastname();
		
		$stream = $this->getDoctrine()
					   ->getRepository('MTIMusicAndMeBundle:Stream')
					   ->findOneById($streamId);
		
		return $this->render(
			'MTIMusicAndMeBundle:Stream:view.html.twig',
			array(
				'is_connected' => $user == null ? false : true,
				'user_name' => $userName,
				'stream' => $stream,
			)
		);
	}
}