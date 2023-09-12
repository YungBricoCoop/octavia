<?php

namespace ybc\octavia\Utils;

class Session
{
	public function __construct($options = [])
	{
		if (session_status() == PHP_SESSION_NONE) {
			session_start($options);
		}
	}

	public function set($key, $value)
	{
		$_SESSION[$key] = $value;
	}

	public function get($key)
	{
		return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
	}

	public function remove($key)
	{
		if (isset($_SESSION[$key])) {
			unset($_SESSION[$key]);
		}
	}

	public function destroy()
	{
		session_destroy();
	}

	public function is_logged()
	{
		return isset($_SESSION['is_logged']) && $_SESSION['is_logged'] == 1;
	}

	public function is_admin()
	{
		return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
	}
}
