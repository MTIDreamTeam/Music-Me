<?php

namespace MTI\MusicAndMeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\SessionStorage\PdoSessionStorage;

use MTI\MusicAndMeBundle\Entity\Stream;
use MTI\MusicAndMeBundle\Entity\PlayedStream;
use MTI\MusicAndMeBundle\Entity\Vote;
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
		
		foreach ($nextRecords as $nextRecord)
		{
			$nextRecordsHasVoted[] = false;
			
			$music = $nextRecord->getMusic();
			$album = $nextRecord->getMusic()->getAlbum();
			// $votes = $nextRecord->getVotes();
			$votes = $this->getDoctrine()
						  ->getRepository('MTIMusicAndMeBundle:Vote')
						  ->findByStreamRecord($nextRecord->getId());
			// foreach($nextRecord->getVotes() as $v)
			// {
			// 	echo $v->getId() . '<br/';
			// }
			// die();
			// var_dump(count($votes));die();
			$nextRecordsVotes[] = count($votes);
			$nextMusicId[] = $music->getId();
			$nextMusicTitle[] = $music->getTitle();
			$nextMusicArtist[] = $album->getArtiste()->getName();
			$nextMusicAlbum[] = $album->getTitle();
			
			foreach ($votes as $vote)
			{
				if ($vote->getUser()->getId() == $user->getId())
				{
					$nextRecordsHasVoted[] = true;
					break;
				}
			}
		}
		
		$currentRecordQuery = $this->getDoctrine()
								   ->getRepository('MTIMusicAndMeBundle:StreamRecords')
								   ->createQueryBuilder('record')
								   ->where("record.played <= '" . $now->format('Y-m-d H:i:s') . "'")
								   ->andWhere("record.stream = " . $streamId)
								   ->orderBy('record.played', 'DESC')
								   ->getQuery();
		$currentRecordResult = $currentRecordQuery->getResult();
		// var_dump(count($currentRecordResult));die();
		
		$currentRecord = null;
		
		if (count($currentRecordResult))
		{
			$lastEndTime = $currentRecordResult[0]->getPlayed()->getTimestamp() + $currentRecordResult[0]->getMusic()->getDuree();
			if ($lastEndTime > $now->getTimestamp())
			{
				$currentRecord = $currentRecordResult[0];
			}
		}
		
		return $this->render(
			'MTIMusicAndMeBundle:Stream:view.html.twig',
			array(
				'is_connected' => $user == null ? false : true,
				'user_name' => $userName,
				'stream' => $stream,
				'records_count' => $recordsCount,
				'current_record' => $currentRecord,
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
	
	public function currentSongAction(Request $request)
	{
		$streamId = $request->attributes->get('stream_id');
		
		$now = new \DateTime();
		$currentRecordQuery = $this->getDoctrine()
								   ->getRepository('MTIMusicAndMeBundle:StreamRecords')
								   ->createQueryBuilder('record')
								   ->where("record.played <= '" . $now->format('Y-m-d H:i:s') . "'")
								   ->andWhere("record.stream = " . $streamId)
								   ->orderBy('record.played', 'DESC')
								   ->getQuery();
		$currentRecordResult = $currentRecordQuery->getResult();
		$currentRecord = null;
		
		if (count($currentRecordResult))
		{
			$lastEndTime = $currentRecordResult[0]->getPlayed()->getTimestamp() + $currentRecordResult[0]->getMusic()->getDuree();
			
			if ($lastEndTime > $now->getTimestamp())
			{
				$result = $currentRecordResult[0];
				return new Response(
					json_encode(
						array(
							'record' => array(
								'name' => $result->getMusic()->getTitle(),
								'artist' => $result->getMusic()->getAlbum()->getArtiste()->getName(),
								'album' => $result->getMusic()->getAlbum()->getTitle()
							)
						)
					)
				);
			}
		}
		return new Response(
			json_encode(
				array(
					'record' => null
				)
			)
		);
	}
	
	public function stopAction(Request $request)
	{
		$session = $this->get('session');
		$session->set('playing_stream', null);
		$session->set('show_player', false);
		
		return new Response(
			json_encode(array('' => ''))
		);
	}
	
	public function playAction(Request $request)
	{
		$streamId = $request->attributes->get('stream_id');
		
		if (!Authentication::isAuthenticated($request))
			return $this->redirect($this->generateUrl('MTIMusicAndMeBundle_login'));
		
		$session = $this->get('session');
		
		$data = json_decode($this->getRequest()->getContent(), true);
		
		$now = new \DateTime();
		$currentRecordQuery = $this->getDoctrine()
								   ->getRepository('MTIMusicAndMeBundle:StreamRecords')
								   ->createQueryBuilder('record')
								   ->where("record.played <= '" . $now->format('Y-m-d H:i:s') . "'")
								   ->andWhere("record.stream = " . $streamId)
								   ->orderBy('record.played', 'DESC')
								   ->getQuery();
		$currentRecordResult = $currentRecordQuery->getResult();
		$currentRecord = null;
		
		if (count($currentRecordResult))
		{
			$lastEndTime = $currentRecordResult[0]->getPlayed()->getTimestamp() + $currentRecordResult[0]->getMusic()->getDuree();
			if ($lastEndTime > $now->getTimestamp())
			{
				$currentRecord = $currentRecordResult[0];

				// Logs in Database
				if ($session->get('show_player') == false)
				{
					$user = $this->getDoctrine()
								 ->getRepository('MTIMusicAndMeBundle:User')
								 ->findOneById($session->get('user_id'));
					$stream = $this->getDoctrine()
								   ->getRepository('MTIMusicAndMeBundle:Stream')
								   ->findOneById($streamId);
				
					$playedStream = new PlayedStream();
					$playedStream->setUser($user);
					$playedStream->setStream($stream);
					$em = $this->getDoctrine()->getEntityManager();
					$em->persist($playedStream);
					$em->flush();
				}
				
				$session->set('show_player', true);
				$session->set('playing_stream', $streamId);
				
				
				return new Response(
					json_encode(
						array(
							'stop' => false,
							'path' => $currentRecord->getMusic()->getWebPath(),
							'time' => $now->getTimestamp() - $currentRecord->getPlayed()->getTimestamp()
						)
					)
				);
			}
		}
		
		$session->set('playing_stream', null);
		$session->set('show_player', false);
		return new Response(
			json_encode(
				array(
					'stop' => true
				)
			)
		);
	}
	
	private function getCurrentMusic($stream)
	{
		$record = $this->getCurrentRecord($stream);
		if ($record)
			return $record->getMusic();
		return null;
	}
	
	private function getCurrentRecord($stream)
	{
		$now = new \DateTime();
		$currentRecordQuery = $this->getDoctrine()
								   ->getRepository('MTIMusicAndMeBundle:StreamRecords')
								   ->createQueryBuilder('record')
								   ->where("record.played <= '" . $now->format('Y-m-d H:i:s') . "'")
								   ->andWhere("record.stream = " . $stream->getId())
								   ->orderBy('record.played', 'DESC')
								   ->getQuery();
		$currentRecordResult = $currentRecordQuery->getResult();
		$currentRecord = null;
		var_dump($currentRecordResult);die();
		if (count($currentRecordResult))
		{
			$lastEndTime = $currentRecordResult[0]->getPlayed()->getTimestamp() + $currentRecordResult[0]->getMusic()->getDuree();
			
			if ($lastEndTime > $now->getTimestamp())
				return $currentRecordResult[0];
		}
		
		return null;
	}
	
	private function reorderStreamRecords(StreamRecord $streamRecord)
	{
		$currentRecord = $this->getCurrentRecord($streamRecord);
		$currentMusic = $currentRecord->getMusic();
		
		$now = new \DateTime();
		$endMusic = new \DateTime();
		$endMusic->setTimestamp($currentRecord->getPlayed()->getTimestamp() - $currentMusic->getDuree());
		
		$query = $this->getDoctrine()
					  ->getRepository('MTIMusicAndMeBundle:StreamRecords')
					  ->createQueryBuilder('record')
					  ->where("record.played <= '" . $now->format('Y-m-d H:i:s') . "'")
					  ->andWhere("record.played >= '" . $endMusic->format('Y-m-d H:i:s') . "'")
					  ->andWhere("record.stream = " . $streamRecord->getStream()->getId())
					  ->orderBy("record.played", "ASC")
					  ->getQuery();
		
		// Gets the records between the one played and the one voted
		$records = $query->getResult();
		$recordsCount = count($records);
		$foundInversion = false;
		
		// Prepares an Entity Manager if we need to update values in the Database
		$em = $this->getDoctrine()->getEntityManager();
		
		for ($i = 0; $i < $recordsCount; $i++)
		{
			if ($foundInversion)
			{
				$newDate = new \DateTime();
				$newDate->setTimestamp($records[$i - 1]->getPlayed()->getTimestamp() + $records[$i - 1]->getMusic()->getDuree());
				$records[$i]->setPlayed($newDate);
				$em->persist($records[$i]);
			}
			else
			{
				if ($i < $recordsCount)
				{
					$betterRankedRecord = $records[$i + 1];
					if (count($betterRankedRecord.getVotes()) < count($streamRecord.getVotes()))
					{
						$foundInversion = true;
						// Sets a more recent date to the promoted song
						$streamRecord->setPlayed($betterRankedRecord->getPlayed());
						$em->persist($streamRecord);
					}
				}
			}
		}
		
		if ($foundInversion)
		{
			$em->flush();
			return true;
		}
		return false;
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
		
		$user = $this->getDoctrine()
					 ->getRepository('MTIMusicAndMeBundle:User')
					 ->find($session->get('user_id'));
		
		// If the request sent the record id (i.e. the song was voted from the stream view)
		if ($data['record'])
		{
			$streamRecord = $this->getDoctrine()
								 ->getRepository('MTIMusicAndMeBundle:Stream')
								 ->findOneById($data['record']);
			
			$vote = new Vote();
			$vote->setUser($user);
			$vote->setStreamRecord($streamRecord);
			
			$em = $this->getDoctrine()->getEntityManager();
			$em->persist($vote);
			$em->flush();
			
			$this->reorderStreamRecords($streamRecord);
			
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
		// We need to know if we have to create a new stream record or find the existing one
		else
		{
			$currentRecord = $this->getCurrentRecord($stream);
			
			// There is a song playing
			if ($currentRecord)
			{
				$now = new \DateTime();
				
				$query = $this->getDoctrine()
							  ->getRepository('MTIMusicAndMeBundle:StreamRecords')
							  ->createQueryBuilder('record')
							  ->where("record.played < '" . $currentRecord->getPlayed()->format('Y-m-d H:i:s') . "'")
							  ->andWhere("record.played >= '" . $now->format('Y-m-d H:i:s') . "'")
							  ->andWhere("record.stream = " . $stream->getId())
							  ->orderBy("record.played", "ASC")
							  ->getQuery();
				$upcompingStreams = $query->getResult();
			
				// There are upcoming Stream Records
				if (count($upcompingStreams))
				{
					$foundStreamRecordInUpcomings = false;
					foreach ($upcompingStreams as $upcompingStream)
					{
						if ($upcompingStream->getMusic()->getId() == $music->getId())
						{
							$foundStreamRecordInUpcomings = true;
						
							// Creates Vote with the stream found
							$vote = new Vote();
							$vote->setUser($user);
							$vote->setStreamRecord($upcompingStream);
						
							// Save the vote in DB
							$em->persist($vote);
							$em->flush();
						
							break;
						}
					}
					
					// The voted record is in the upcomings
					if ($foundStreamRecordInUpcomings)
					{
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
					// The voted record is not in the upcomings
					else
					{
						// We create an new Record after all the existing ones
						$newRecord = new StreamRecords();
						$newRecord->setStream($stream);
						$newRecord->setMusic($music);
					
						$lastStreamRecord = $upcompingStreams[count($upcompingStreams) - 1];
				
						$timeToPlay = new \DateTime();
						$timeToPlay->setTimestamp($lastStreamRecord->getPlayed()->getTimestamp() + $lastStreamRecord->getMusic()->getDuree());
						$newRecord->setPlayed($timeToPlay);
				
						// Create a new vote with that record
						$vote = new Vote();
						$vote->setUser($user);
						$vote->setStreamRecord($newRecord);
				
						$em = $this->getDoctrine()->getEntityManager();
						$em->persist($newRecord);
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
				// There is no upcomming StreamRecord
				else
				{
					// CREATE A NEW RECORD after the currently playing songs
					$newRecord = new StreamRecords();
					$newRecord->setStream($stream);
					$newRecord->setMusic($music);
				
					$timeToPlay = new \DateTime();
					$timeToPlay->setTimestamp($currentRecord->getPlayed()->getTimestamp() + $currentRecord->getMusic()->getDuree());
					$newRecord->setPlayed($timeToPlay);
				
					// Create a new vote with that record
					$vote = new Vote();
					$vote->setUser($user);
					$vote->setStreamRecord($newRecord);
				
					$em = $this->getDoctrine()->getEntityManager();
					$em->persist($newRecord);
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
			else
			{
				$now = new \DateTime();
				
				// CREATE A NEW RECORD which will start playing immeditately
				$newRecord = new StreamRecords();
				$newRecord->setStream($stream);
				$newRecord->setMusic($music);
				
				// Sets the time to play to now
				$newRecord->setPlayed($now);
				
				// Create a new vote with that record
				$vote = new Vote();
				$vote->setUser($user);
				$vote->setStreamRecord($newRecord);
				
				$em = $this->getDoctrine()->getEntityManager();
				$em->persist($newRecord);
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
}
