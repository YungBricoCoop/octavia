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

	/**
	 * Set a session variable
	 * @param string $key
	 * @param mixed $value
	 * @example $session->set('user', ['firstname' => 'Octavia', 'lastname' => 'Blake']);
	 * @return void
	 */
	public function set($key, $value)
	{
		$_SESSION[$key] = $value;
	}

	/**
	 * Get a session variable
	 * @param string $key
	 * @example $session->get('user');
	 * @return mixed
	 */
	public function get($key)
	{
		return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
	}

	/**
	 * Remove a session variable
	 * @param string $key
	 * @example $session->remove('user');
	 * @return bool
	 */
	public function remove($key)
	{
		if (isset($_SESSION[$key])) {
			unset($_SESSION[$key]);
			return true;
		}
		return false;
	}

	/**
	 * Destroy the session
	 * @return bool
	 */
	public function destroy()
	{
		return session_destroy();
	}

	/**
	 * Check if the user is logged
	 * @return bool
	 */
	public function is_logged()
	{
		return isset($_SESSION['is_logged']) && $_SESSION['is_logged'] == 1;
	}

	/**
	 * Check if the user is admin
	 * @return bool
	 */
	public function is_admin()
	{
		return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
	}
}
