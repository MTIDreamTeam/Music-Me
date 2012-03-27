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


class SearchController extends Controller
{
  private function getSong($streamId)
  {
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
			  $currentRecord = $result;
		  }
	  }
	  return $currentRecord;
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

    if (0 === strpos($this->getRequest()->headers->get('Content-Type'), 'application/json'))
    {
	    $data = json_decode($this->getRequest()->getContent(), true);
	    $toSearch = $data['searchFlux'];
    }
    else
	    $toSearch = $request->request->get('searchFlux');

    $listeStream = $this->getDoctrine()
    ->getEntityManager()
    ->getRepository('MTIMusicAndMeBundle:Stream')
    ->searchStream($toSearch);

    $listCur = array();
    foreach($listeStream as $stream) {
        $listCur[$stream->getId()] = $this->getSong($stream->getId());
    }
    
    
	return $this->render(
		'MTIMusicAndMeBundle:Search:resultSearch.html.twig',
		array(
			'is_connected' => $user == null ? false : true,
			'user_name' => $userName,
			'toSearch' => $toSearch,
			'listeStream' => $listeStream,
			'listCur' => $listCur
		)
	);
  }
}
