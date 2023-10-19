<?php

namespace ybc\octavia\Middleware;

use ybc\octavia\Request;
use ybc\octavia\Response;
use ybc\octavia\Router\Route;

class Context
{
    public Request $request;
    public ?Route $route;
    public ?Response $response;
	public $terminate_chain;

    public function __construct(Request $request, ?Route $route = null, ?Response $response = null)
	{
		$this->request = $request;
		$this->route = $route;
		$this->response = $response;
		$this->terminate_chain = false;
	}
}
