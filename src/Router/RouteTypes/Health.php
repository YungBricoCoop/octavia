<?php

namespace ybc\octavia\Router\RouteTypes;

use ybc\octavia\Router\Route;
use ybc\octavia\Utils\Utils;
use UnauthorizedException;

class Health extends RouteType
{
	public static $http_method = "GET";
	private bool $auth_required = false;
	private string $username;
	private string $password;

	public function __construct(bool $auth_required = OCTAVIA_HEALTH_AUTH_REQUIRED, string $username = OCTAVIA_HEALTH_USERNAME, string $password = OCTAVIA_HEALTH_PASSWORD)
	{
		$this->auth_required = $auth_required;
		$this->username = $username;
		$this->password = $password;
	}

	public function handle(Route $route)
	{
		if (!$this->auth_required) return;

		$valid = Utils::validate_basic_auth($this->username, $this->password);
		if (!$valid) {
			throw new UnauthorizedException();
		};
	}
}
