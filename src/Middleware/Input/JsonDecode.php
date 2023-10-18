<?php

namespace ybc\octavia\Middleware\Input;

use ybc\octavia\Middleware\{Context, Middleware};

class JsonDecode extends Middleware
{
	public function handle(Context $ctx): Context
	{
		$ctx->request->body = json_decode(file_get_contents('php://input'), true);
		return $ctx;
	}
}
