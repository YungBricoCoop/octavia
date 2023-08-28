<?php

namespace Vendor\YbcFramework\Interfaces;

interface RouteInterface
{
	public function login();
	public function admin();
	public function query($params);
	public function q($params);
	public function body($params);
	public function b($params);
}
