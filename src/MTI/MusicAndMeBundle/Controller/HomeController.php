<?php

namespace MTI\MusicAndMeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\SessionStorage\PdoSessionStorage;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

class HomeController extends Controller
{
    
    public function indexAction(Request $request)
    {
		$session = $this->get('session');
		
		$user = $this->getDoctrine()
					 ->getRepository('MTIMusicAndMeBundle:User')
					 ->find($session->get('user_id'));
		
		$userName = $user == null ? null : $user->getFirstname() . ' ' . $user->getLastname();
		
		if ($userName)
		{
			$streams = $this->getDoctrine()
							->getRepository('MTIMusicAndMeBundle:Stream')
							->findBy(array('owner' => $user->getId()));
			
			// First, we get the IDs of the last streams played
			$em = $this->getDoctrine()->getEntityManager();
			$lastPlayedQuery = $em->getConnection()->prepare('SELECT A.id FROM `played_stream` A
													INNER JOIN (
														SELECT id, user, MAX(created) AS created, stream
														FROM `played_stream` GROUP BY stream
													) B
													ON A.stream = B.stream AND A.created = B.created WHERE A.user = ' . $user->getId());
			$lastPlayedQuery->execute();
			$lastPlayedIds = $lastPlayedQuery->fetchAll();
			$lastPlayedIds = array_map(function($entry) { return $entry['id']; }, $lastPlayedIds);
			
			// Then, we retrieve the objects from that list of IDs
			$lastPlayedQuery = $this->getDoctrine()
									->getRepository('MTIMusicAndMeBundle:PlayedStream')
									->createQueryBuilder('played');
			for ($i = 0; $i < count($lastPlayedIds); $i++) {
				if ($i == 0)
					$lastPlayedQuery = $lastPlayedQuery->where('played.id = ' . $lastPlayedIds[$i]);
				else
					$lastPlayedQuery = $lastPlayedQuery->orWhere('played.id = ' . $lastPlayedIds[$i]);
			}
			$lastPlayedQuery = $lastPlayedQuery->orderBy('played.created', 'DESC')
											   ->getQuery();
			$lastPlayedResults = $lastPlayedQuery->getResult();
			
			
			$lastPlayedCurrentSongs = array();
			$now = new \DateTime();
			foreach ($lastPlayedResults as $streamRecord)
			{
				$currentRecordQuery = $this->getDoctrine()
										   ->getRepository('MTIMusicAndMeBundle:StreamRecords')
										   ->createQueryBuilder('record')
										   ->where("record.played <= '" . $now->format('Y-m-d H:i:s') . "'")
										   ->andWhere("record.stream = " . $streamRecord->getStream()->getId())
										   ->orderBy('record.played', 'DESC')
										   ->getQuery();
				$currentRecordResult = $currentRecordQuery->getResult();
				
				if (count($currentRecordResult))
				{
					$lastEndTime = $currentRecordResult[0]->getPlayed()->getTimestamp() + $currentRecordResult[0]->getMusic()->getDuree();
					if ($lastEndTime > $now->getTimestamp())
					{
						$lastPlayedCurrentSongs[] = $currentRecordResult[0];
						continue;
					}
				}
				$lastPlayedCurrentSongs[] = null;
			}
			
			$myStreamsCurrentSongs = array();
			$now = new \DateTime();
			foreach ($streams as $stream)
			{
				$currentRecordQuery = $this->getDoctrine()
										   ->getRepository('MTIMusicAndMeBundle:StreamRecords')
										   ->createQueryBuilder('record')
										   ->where("record.played <= '" . $now->format('Y-m-d H:i:s') . "'")
										   ->andWhere("record.stream = " . $stream->getId())
										   ->orderBy('record.played', 'DESC')
										   ->getQuery();
				$currentRecordResult = $currentRecordQuery->getResult();
				
				if (count($currentRecordResult))
				{
					$lastEndTime = $currentRecordResult[0]->getPlayed()->getTimestamp() + $currentRecordResult[0]->getMusic()->getDuree();
					if ($lastEndTime > $now->getTimestamp())
					{
						$myStreamsCurrentSongs[] = $currentRecordResult[0];
						continue;
					}
				}
				$myStreamsCurrentSongs[] = null;
			}
			
			$templateName = null;
			if (0 === strpos($this->getRequest()->headers->get('Content-Type'), 'application/json'))
				$templateName = 'MTIMusicAndMeBundle:Home:indexLoggedIn.ajax.twig';
			else
				$templateName = 'MTIMusicAndMeBundle:Home:indexLoggedIn.html.twig';
			
			return $this->render($templateName, array(
				'is_connected' => $user == null ? false : true,
				'user_name' => $userName,
				'my_streams' => $streams,
				'my_streams_current_songs' => $myStreamsCurrentSongs,
				'last_played' => $lastPlayedResults,
				'last_played_current_songs' => $lastPlayedCurrentSongs,
			));
		}
		else
		{
			$templateName = null;
			if (0 === strpos($this->getRequest()->headers->get('Content-Type'), 'application/json'))
				$templateName = 'MTIMusicAndMeBundle:Home:index.ajax.twig';
			else
				$templateName = 'MTIMusicAndMeBundle:Home:index.html.twig';
			
			return $this->render($templateName, array(
				'is_connected' => $user == null ? false : true,
				'user_name' => $userName,
			));
		}
    }
}
