<?php

namespace ybc\octavia\Middleware\Input;

use ybc\octavia\Enums\MiddlewareStages;
use ybc\octavia\Middleware\{Middleware, Context};
use ybc\octavia\Utils\Session;
use ybc\octavia\{RateLimitExceededException};

class SessionRateLimit extends Middleware
{
	//FIXME: Should be BEFORE_ROUTING, but can't get the correct path from the $ctx->request for now
	public $stage = MiddlewareStages::AFTER_ROUTING;

	private $rate_limit_per_minute;
	private $timeout_duration_s;

	/**
	 * Rate limit requests per minute using the session
	 * Using this is not recommended since the user can easily bypass it by deleting the session cookie
	 * @param int $rate_limit_per_minute The number of requests allowed per minute, default 100
	 * @param int $timeout_duration_s The number of seconds to wait before allowing requests again, default 300
	 * @throws \Exception
	 */
	public function __construct($rate_limit_per_minute = 100, $timeout_duration_s = 300)
	{
		$this->rate_limit_per_minute = $rate_limit_per_minute;
		$this->timeout_duration_s = $timeout_duration_s;
	}

	public function handle(Context $ctx): Context
	{
		// get the current session
		$session = Session::get_instance();
		$current_time = time();
		$path = $ctx->route->path;

		$timestamp_key = "{$path}_timestamp";
		$counter_key = "{$path}_counter";
		$timeout_key = "{$path}_timeout";

		if (!$session->exists($timestamp_key)) {
			$session->set($timestamp_key, $current_time);
		}
		if (!$session->exists($counter_key)) {
			$session->set($counter_key, 0);
		}

		// if rate is already exceeded, throw an exception
		if ($session->exists($timeout_key) && $current_time < $session->get($timeout_key)) {
			throw new RateLimitExceededException();
		}

		$time_passed = $current_time - $session->get($timestamp_key);
		if ($time_passed >= 60) {
			$session->set($counter_key, 1);
			$session->set($timestamp_key, $current_time);
			return $ctx;
		}

		$this->increment_and_check($session, $counter_key, $timeout_key);

		return $ctx;
	}

	private function increment_and_check($session, $counter_key, $timeout_key)
	{
		$counter = $session->get($counter_key) + 1;
		$session->set($counter_key, $counter);

		// if rate is exceeded, set the timeout and throw an exception
		if ($counter > $this->rate_limit_per_minute) {
			$session->set($timeout_key, time() + $this->timeout_duration_s);
			throw new RateLimitExceededException();
		}
	}
}
