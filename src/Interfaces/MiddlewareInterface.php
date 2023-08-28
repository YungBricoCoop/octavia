<?php

namespace Vendor\YbcFramework\Interfaces;

use Vendor\YbcFramework\Request;
use Vendor\YbcFramework\Response;

interface MiddlewareInterface
{
	public function handle_before(Request $request);
	public function handle_after(Response $request);
}
