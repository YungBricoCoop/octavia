<?php

namespace Vendor\YbcFramework;

class Utils
{
	/**
	 * Convert an endpoint to a function name
	 * For example: /api/v1/users/1 => api_v1_users_1
	 * @param string $endpoint
	 * @return string
	 */
	public static function endpoint_to_function_name($endpoint)
	{
		$endpoint = str_replace('/', '_', $endpoint);
		$endpoint = str_replace('-', '_', $endpoint);
		if (substr($endpoint, 0, 1) === '_') {
			$endpoint = substr($endpoint, 1);
		}
		return $endpoint;
	}
}
