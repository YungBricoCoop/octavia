<?php

namespace ybc\octavia\Config;

use ybc\octavia\Constants\FrameworkConfig;
use ybc\octavia\Utils\Utils;

class Config
{

	/**
	 * Load config from .env file or from given array
	 * @param string|string $user_config
	 * @return void
	 */
	public static function load($user_config = ".env")
	{
		define("CONFIG_PREFIX", "OCTAVIA_");

		$use_env = is_string($user_config);
		if ($use_env) {
			Utils::load_env($user_config);
		}

		$default_config = FrameworkConfig::get();

		//TODO: Load config from given .env file
		foreach ($default_config as $key => $default_value) {
			$env_value = getenv($key);
			if ($env_value) {
				define(CONFIG_PREFIX . $key, $env_value);
				continue;
			}

			$config_value = isset($user_config[$key]) ? $user_config[$key] : $default_value;
			define(CONFIG_PREFIX . $key, $config_value);
		}
	}
}
