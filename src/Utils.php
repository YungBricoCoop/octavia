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

	/**
	 * Send a response
	 * @param mixed $data The data to send
	 * @param mixed $error The error to send
	 * @param int $status The status code to send
	 * @return void
	 */
	public static function response($data, $error = false, $status = 200)
	{
		$response = [
			"data" => $data,
		];
		
		// only include the error key if there is an error
		if ($error) {
			$response["error"] = $error;
		}
		
		http_response_code($status);
		header('Content-Type: application/json');
		echo json_encode($response);
		exit;
	}
}
