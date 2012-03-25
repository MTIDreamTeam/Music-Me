<?php

namespace MTI\MusicAndMeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\SessionStorage\PdoSessionStorage;

use MTI\MusicAndMeBundle\Entity\Stream;
use MTI\MusicAndMeBundle\Entity\Musique;
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
		
		$now = new \DateTime();
		$nextRecordsQuery = $this->getDoctrine()
								 ->getRepository('MTIMusicAndMeBundle:StreamRecords')
								 ->createQueryBuilder('record')
								 ->where("record.played > '" . $now->format('Y-m-d H:i:s') . "'")
								 ->andWhere("record.stream = " . $streamId)
								 ->orderBy('record.played', 'ASC')
								 ->getQuery();
		$nextRecords = $nextRecordsQuery->getResult();
		
		$recordsCount = count($nextRecords);
		$nextRecordsVotes = array();
		$nextRecordsHasVoted = array();
		$nextMusicId = array();
		$nextMusicTitle = array();
		$nextMusicArtist = array();
		$nextMusicAlbum = array();
		$nextMusicCover = array();
		
		for ($i = 0; $i < count($nextRecords); $i++)
		{
			$nextRecordsHasVoted[$i] = false;
			
			$music = $nextRecords[$i]->getMusic();
			$album = $nextRecords[$i]->getMusic()->getAlbum();
			$votes = $nextRecords[$i]->getVotes();
			
			$nextRecordsVotes[$i] = count($votes);
			$nextMusicId[$i] = $music->getId();
			$nextMusicTitle[$i] = $music->getTitle();
			$nextMusicArtist[$i] = $album->getArtiste()->getName();
			$nextMusicAlbum[$i] = $album->getTitle();
			
			foreach ($votes as $vote)
			{
				if ($vote->getUser()->getId() == $user->getId())
				{
					$nextRecordsHasVoted[$i] = true;
					break;
				}
			}
		}
		
		return $this->render(
			'MTIMusicAndMeBundle:Stream:view.html.twig',
			array(
				'is_connected' => $user == null ? false : true,
				'user_name' => $userName,
				'stream' => $stream,
				'records_count' => $recordsCount,
				'next_records' => $nextRecords,
				'next_records_votes' => $nextRecordsVotes,
				'next_records_has_voted' => $nextRecordsHasVoted,
				'next_musics_id' => $nextMusicId,
				'next_musics_title' => $nextMusicTitle,
				'next_musics_artist' => $nextMusicArtist,
				'next_musics_album' => $nextMusicAlbum,
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

		$stream = $this->getDoctrine()
					   ->getRepository('MTIMusicAndMeBundle:Stream')
					   ->findOneById($data['stream']);

		if ($music == null)
		{
			return new Response(
				json_encode(
					array(
						'alert' => array(
							'type' => 'error',
							'title' => 'Le vote n\'a pas été pris en compte',
							'message' => 'La musique demandée pour le vote n\'existe pas',
						)
					)
				)
			);
		}
		if ($stream == null)
		{
			return new Response(
				json_encode(
					array(
						'alert' => array(
							'type' => 'error',
							'title' => 'Le vote n\'a pas été pris en compte',
							'message' => 'Le flux demandé pour le vote n\'existe pas',
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
		
		$user = $this->getDoctrine()
					 ->getRepository('MTIMusicAndMeBundle:User')
					 ->find($session->get('user_id'));
		
		// There is a song being played
		if (count($records))
		{
			$streamRecord = $records[0];
			
			$vote = new Vote();
			$vote->setUser($user);
			$vote->setStreamRecord($streamRecord);
			
			$em = $this->getDoctrine()->getEntityManager();
			$em->persist($vote);
			$em->flush();
			
			$query = $this->getDoctrine()
						  ->getRepository('MTIMusicAndMeBundle:StreamRecords')
						  ->createQueryBuilder('record')
						  ->where("record.played > '" . $streamRecord->getPlayed()->format('Y-m-d H:i:s') . "'")
						  ->orderBy('record.played', 'ASC')
						  ->getQuery();
			$nextRecords = $query->getResult();
		}
		// No song is being played, play this one !
		else
		{
			$streamRecord = new StreamRecords();
			$streamRecord->setStream($stream);
			$streamRecord->setMusic($music);
			
			$vote = new Vote();
			$vote->setUser($user);
			$vote->setStreamRecord($streamRecord);
			
			$em = $this->getDoctrine()->getEntityManager();
			$em->persist($streamRecord);
			$em->flush();
			$em->persist($vote);
			$em->flush();
			
			return new Response(
				json_encode(
					array(
						'alert' => array(
							'type' => 'success',
							'title' => 'Le vote a bien été pris en compte',
							'message' => 'Vous avez voté pour le morceau '.$music->getTitle(),
						)
					)
				)
			);
		}
	}
}
