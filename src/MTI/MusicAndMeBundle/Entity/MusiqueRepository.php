<?php

namespace MTI\MusicAndMeBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * MusiqueRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MusiqueRepository extends EntityRepository
{
    public function searchMusic($toSearch)
    {
      $query = $this->_em->createQuery('SELECT m, a, art FROM MTI\MusicAndMeBundle\Entity\Musique m
						  JOIN m.album a
						  JOIN a.artiste art
						  WHERE LOWER(m.title) LIKE :search
						  OR LOWER(a.title) LIKE :search
						  OR LOWER(art.name) LIKE :search');
      $query->setParameter('search', '%'.strtolower($toSearch).'%');
      return $query->getResult();
    }
}
