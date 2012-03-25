<?php

namespace MTI\MusicAndMeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\SessionStorage\PdoSessionStorage;

use MTI\MusicAndMeBundle\Entity\Stream;
use MTI\MusicAndMeBundle\Entity\StreamRecords;
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
					
					return $this->redirect($this->generateUrl('MTIMusicAndMeBundle_streamIndex'));
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
		
		$streamRecords = $this->getDoctrine()
							  ->getRepository('MTIMusicAndMeBundle:StreamRecords')
							  ->findByStream($streamId);

		$lastRecord = $streamRecords[0];
		$date = new \DateTime();
		
		$repo = $this->getDoctrine()
					 ->getRepository('MTIMusicAndMeBundle:Vote');
		
		// $query = $repository->createQueryBuilder('vote')
		// 					->where('vote.created > ' . $lastRecord->getPlayed());
		
		// var_dump($lastRecord->isPlaying());
		
		// die();
		
		// $votes = $this->getDoctrine()
		// 			  ->getRepository('MTIMusicAndMeBundle:Vote')
		// 			  ->findByStream($streamId);

		return $this->render(
			'MTIMusicAndMeBundle:Stream:view.html.twig',
			array(
				'is_connected' => $user == null ? false : true,
				'user_name' => $userName,
				'stream' => $stream,
			)
		);
	}
	
	public function voteAction(Request $request)
	{
		$session = $this->get('session');
		$data = json_decode($this->getRequest()->getContent(), true);
		
		$music = $this->getDoctrine()
					  ->getRepository('MTIMusicAndMeBundle:Musique')
					  ->findOneById($data['music']);
		if ($music == null)
		{
			return new Response(
				json_encode(
					array(
						'error' => array(
							'title' => 'Le vote n\'a pas été pris en compte',
							'message' => 'La musique demandée pour le vote n\'existe pas',
						)
					)
				)
			);
		}
		
		$now = new \DateTime();
		$endMusic = new \DateTime();
		$endMusic->setTimestamp($now->getTimestamp() - $music->getDuree());
		
		$query = $this->getDoctrine()
					  ->getRepository('MTIMusicAndMeBundle:StreamRecords')
					  ->createQueryBuilder('record')
					  ->where("record.played <= '" . $now->format('Y-m-d H:i:s') . "'")
					  ->andWhere("record.played > '" . $endMusic->format('Y-m-d H:i:s') . "'")
					  ->getQuery();
		
		$records = $query->getResult();
		
		// There is a song being played
		if (count($records))
		{
			$playedSong = $records[0];
			return new Response(json_encode(array($records[0]->getPlayed())));
		}
		
		return new Response(json_encode(array('name' => 'plop', 'error' => null)));
	}
}
