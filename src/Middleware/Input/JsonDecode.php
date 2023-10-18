<?php

namespace ybc\octavia\Middleware\Input;

use ybc\octavia\Interfaces\InputMiddlewareInterface;
use ybc\octavia\Request;

class JsonDecode implements InputMiddlewareInterface
{
	public function handle(Request $request)
	{
		$request->body = json_decode(file_get_contents('php://input'), true);
		return $request;
	}
}
