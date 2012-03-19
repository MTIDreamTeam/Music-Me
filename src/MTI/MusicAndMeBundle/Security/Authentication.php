<?php

namespace MTI\MusicAndMeBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use MTI\MusicAndMeBundle\Entity\User;

class Authentication
{
	public static function isAuthenticated(Request $request)
	{
		// Retrieves the calling controller
		$backtrace = debug_backtrace();
		$controller = $backtrace[1]['object'];
		
		if ($controller)
		{
			// Gets the user session
			$session = $controller->get('session');
			$route = $request->attributes->get('_route');
			
			// Get the user Id
			$userId = $session->get('user_id');
		
			// If the user is not logged in
			if ($userId == null)
			{
				// Deletes the session
				$session->invalidate();
				// Saves the original route to redirect after a successful login
				$session->set('nextRoute', $route);
					
				return false;
			}
			else
			{
				if ($route == 'MTIMusicAndMeBundle_login');
				{
					$session->set('nextRoute', 'MTIMusicAndMeBundle_homepage');
				}
			}
		}
		
		// Returns true when
		return true;
	}
}
