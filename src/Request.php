<?php

namespace ybc\octavia;

class Request
{
	public $method;
	public $uri;
	public $headers = [];
	public $body;
	public $files = [];
	public $query_params = [];

	public function __construct()
	{
		$this->method = $_SERVER['REQUEST_METHOD'];
		$this->uri = $_SERVER['REQUEST_URI'];
		$this->headers = getallheaders();
		$this->body = file_get_contents('php://input');
		$this->files = $_FILES;
		$this->query_params = $_GET;
	}
}
