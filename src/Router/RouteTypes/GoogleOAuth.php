<?php

namespace ybc\octavia\Router\RouteTypes;

use ybc\octavia\Router\Route;
use ybc\octavia\Utils\GoogleOAuthHandler;

class GoogleOAuth extends RouteType
{
	public static $http_method = "GET";

	public function handle(Route $route): bool
	{

		// get the current_url without the query string
		// this is necessary because after google oauth redirects back to the app
		// a query string with "code=" is appended to the url.
		// but the url that we set in the google client api must match
		// the url that is configured in the OAuth consent screen, and this 
		// url must not have a query string.
		$current_url = 'https://' . $_SERVER['HTTP_HOST'] . rtrim(strtok($_SERVER['REQUEST_URI'], '?'), '/');

		$auth_handler = new GoogleOAuthHandler();
		return $auth_handler->handle_initial_prompt($current_url);
	}
}
