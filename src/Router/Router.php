<?php

namespace Vendor\YbcFramework\Router;

use Vendor\YbcFramework\Utils\Utils;
use Vendor\YbcFramework\Endpoint;

class Router
{
	/**
	 * @var Endpoint[]
	 */
	private $endpoints = [];

	/**
	 * Get the endpoint that matches the given path
	 * Endpoints defined order is important
	 * @param string $http_method The http method
	 * @param string $endpoint_path The path of the endpoint
	 * @return Endpoint|null
	 */
	public function route($http_method, $endpoint_path)
	{
		/* INFO: This code is in comments because I think it could break the order priority of the endpoints
		// if the endpoint does not use dynamic variables, we can directly find it
		if (array_key_exists($key, $this->endpoints)) {
			return $this->endpoints[$key];
		} */

		// match endpoints by number of segments
		$segments = Utils::get_endpoints_path_segments($endpoint_path);
		/**
		 * @var Endpoint[]
		 */
		$matched = [];
		foreach ($this->endpoints as $endpoint) {
			if (count($segments) == count($endpoint->path_segments)) {
				$matched[] = $endpoint;
			}
		}

		// if no endpoint matches, return null
		if (empty($matched)) return null;

		// match the endpoint based on the order of the segements and their name
		foreach ($matched as $endpoint) {
			$endpoint_segments = $endpoint->path_segments;
			$endpoint_matched = true;
			foreach ($segments as $index => $segment) {
				// if the segment is a variable, it matches
				if (substr($endpoint_segments[$index], 0, 1) == "{") continue;

				// if the segment is not a variable, it must match the segment
				if ($segment == $endpoint_segments[$index]) continue;
				$endpoint_matched = false;
				break;
			}
			if (!$endpoint_matched) continue;
			if ($endpoint->http_method != $http_method) continue;
			return $endpoint;
		}

		// if no endpoint matches, return null
		return null;
	}

	/**
	 * Register a new endpoint
	 * @param Endpoint $endpoint
	 */
	public function register($endpoint)
	{
		$key = $endpoint->http_method . $endpoint->path;
		if (array_key_exists($key, $this->endpoints)) {
			throw new \InvalidArgumentException("ENDPOINT_ALREADY_REGISTERED");
		}
		//INFO: Might need to check segments without dynamic variables to avoid conflicts
		$this->endpoints[$key] = $endpoint;
	}

	/**
	 * Get all the registered endpoints
	 * @return Endpoint[]
	 */
	public function getEndpoints()
	{
		return $this->endpoints;
	}
}
