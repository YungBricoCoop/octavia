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
	public static function load($user_config = "")
	{
		$default_config = FrameworkConfig::get();
		if (!$user_config) {
			$user_config = $default_config["CONFIG_ENV_FILE"];
		}
		
		$config_prefix = $default_config["CONFIG_PREFIX"];

		$use_env = is_string($user_config);
		if ($use_env) {
			Utils::load_env($user_config);
		}


		foreach ($default_config as $key => $default_value) {
			$env_value = getenv($key);
			if ($env_value) {
				define($config_prefix . $key, $env_value);
				continue;
			}

			$config_value = isset($user_config[$key]) ? $user_config[$key] : $default_value;
			define($config_prefix . $key, $config_value);
		}
	}
}
