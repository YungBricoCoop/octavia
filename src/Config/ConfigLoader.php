<?php

namespace ybc\octavia\Config;

use ybc\octavia\Constants\Config;

class ConfigLoader
{

	public static function load($user_config = [])
	{
		define("CONFIG_PREFIX", "OCTAVIA_");
		$default_config = Config::get();
		
		//TODO: Load config from given .env file
		foreach ($default_config as $key => $default_value) {
			$env_value = getenv($key);
			$user_value = isset($user_config[$key]) ? $user_config[$key] : $default_value;

			$config_value = $env_value ? $env_value : $user_value;
			define(CONFIG_PREFIX . $key, $config_value);
		}
	}
}
