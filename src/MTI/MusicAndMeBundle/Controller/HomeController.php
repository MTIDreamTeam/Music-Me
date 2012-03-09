<?php

namespace MTI\MusicAndMeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class HomeController extends Controller
{
    
    public function indexAction()
    {
        return $this->render('MTIMusicAndMeBundle:Home:index.html.twig', array());
    }
}
