<?php

namespace ybc\octavia\Utils;

class Session
{
	private static $instance = null;

	public static function get_instance($options = [])
	{
		if (self::$instance === null) {
			self::$instance = new Session($options);
		}
		return self::$instance;
	}

	private function __construct($options = [])
	{
		if (session_status() == PHP_SESSION_NONE) {
			ini_set('session.gc_maxlifetime', OCTAVIA_SESSION_LIFETIME);
			session_name(OCTAVIA_SESSION_NAME);
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
	public function set(string $key, mixed $value)
	{
		$_SESSION[$key] = $value;
	}

	/**
	 * Get a session variable
	 * @param string $key
	 * @example $session->get('user');
	 * @return mixed
	 */
	public function get(string $key)
	{
		return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
	}

	/**
	 * Remove a session variable
	 * @param string $key
	 * @example $session->remove('user');
	 * @return bool
	 */
	public function remove(string $key)
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
		return isset($_SESSION[OCTAVIA_SESSION_IS_LOGGED_KEY]) && $_SESSION[OCTAVIA_SESSION_IS_LOGGED_KEY] == 1;
	}

	/**
	 * Check if the user is admin
	 * @return bool
	 */
	public function is_admin()
	{
		return isset($_SESSION[OCTAVIA_SESSION_IS_ADMIN_KEY]) && $_SESSION[OCTAVIA_SESSION_IS_ADMIN_KEY] == 1;
	}
}
