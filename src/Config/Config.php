<?php

namespace ybc\octavia\Config;

use ybc\octavia\Constants\FrameworkConfig;
use ybc\octavia\Utils\Log;
use ybc\octavia\Utils\Utils;

class Config
{

	/**
	 * Load config from .env file or from given array
	 * @param string|array $user_config
	 * @return void
	 */
	public static function load($user_config = [])
	{
		$default_config = FrameworkConfig::get();
		if (!$user_config) {
			$user_config = $default_config["CONFIG_ENV_FILE"];
		}

		$config_prefix = $default_config["CONFIG_PREFIX"];

		$use_env = is_string($user_config);
		$loaded_env = false;
		if ($use_env) {
			$loaded_env = Utils::load_env($user_config);
		}


		foreach ($default_config as $key => $default_value) {
			$env_value = isset($_ENV[$key]) ? $_ENV[$key] : false;
			if ($env_value) {
				define($config_prefix . $key, $env_value);
				continue;
			}

			$config_value = isset($user_config[$key]) ? $user_config[$key] : $default_value;
			define($config_prefix . $key, $config_value);
		}

		if ($use_env && !$loaded_env) {
			Log::warning("Could not load .env file");
		}
	}

	/**
	 * Get the whole config
	 * @return array
	 */
	public static function get()
	{
		$config_prefix = FrameworkConfig::get()["CONFIG_PREFIX"];
		$config = [];
		foreach (FrameworkConfig::get() as $key => $value) {
			$config[$key] = constant($config_prefix . $key);
		}
		return $config;
	}
}
