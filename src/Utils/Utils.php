<?php

namespace Vendor\YbcFramework\Utils;

use Ramsey\Uuid\Uuid;

class Utils
{

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

	/**
	 * Validate an object by using a class
	 * @param mixed $object The object to validate
	 * @param class $class The class to validate against
	 * @return array [string $property, bool $is_missing, bool $is_wrong_type, string $expected_type, string $actual_type]
	 */
	public static function validate($object, string $className)
	{
		$result = [
			"property" => "",
			"is_missing" => false,
			"is_wrong_type" => false,
			"expected_type" => "",
			"actual_type" => "",
		];
		$object = (object) $object;
		$className = str_starts_with($className, "?") ? substr($className, 1) : $className;


		/* 		if (!class_exists($className)) {
			throw new \InvalidArgumentException("UNKNOWN_CLASS $className");
			return false;
		} */

		$reflection = new \ReflectionClass($className);
		$properties = $reflection->getProperties();

		// Parse all class properties
		foreach ($properties as $property) {
			$proprety_name = $property->getName();
			$type = $property->getType();
			$result["property"] = $proprety_name;

			if (!property_exists($object, $proprety_name) && !$type->allowsNull()) {
				$result["is_missing"] = true;
				return $result;
			}

			if ($type->allowsNull() && $object->$proprety_name === null) {
				continue;
			}

			// Skip if no type is set
			if (!$type) continue;

			$expected_type = (string) $type;
			$actualType = gettype($object->$proprety_name);

			if ($type->isBuiltin()) {
				if (str_starts_with($expected_type, '?')) {
					$expected_type = substr($expected_type, 1);
				}
				if ($expected_type === 'int') {
					$expected_type = 'integer';
				}
				if ($expected_type === 'bool') {
					$expected_type = 'boolean';
				}
				if ($expected_type === 'float') {
					$expected_type = 'double';
				}
				if ($expected_type !== $actualType) {
					$result["is_wrong_type"] = true;
					$result["expected_type"] = $expected_type;
					$result["actual_type"] = $actualType;
					return $result;
				}
				continue;
			}

			/* 			if (!class_exists($className)) {
				throw new \InvalidArgumentException("UNKNOWN_CLASS $className");
				return false;
			} */

			$subResult = self::validate($object->$proprety_name, $expected_type);
			if ($subResult["is_missing"] || $subResult["is_wrong_type"]) {
				return $subResult;
			}

			continue;
		}

		return $result;
	}

	/**
	 * Convert a route to a function name
	 * For example: /api/v1/users/1 => api_v1_users_1
	 * @param string $path
	 * @return string
	 */
	public static function get_route_name($path)
	{
		$path = str_replace('/', '_', $path);
		$path = str_replace('-', '_', $path);
		if (substr($path, 0, 1) === '_') {
			$path = substr($path, 1);
		}
		return $path;
	}

	/**
	 * Get all the segments of a route
	 * @param string $route
	 * @return array
	 * @example /api/v1/{user}/homepage => ["api", "v1", "{user}", "homepage"]
	 */
	public static function get_route_path_segments($path)
	{
		// remove trailing slash if any
		if (substr($path, -1) == "/") {
			$path = substr($path, 0, -1);
		}
		// Split by slash
		$segments = explode("/", $path);
		$segments = array_filter($segments, function ($segment) {
			return $segment != "";
		});
		return $segments;
	}

	/**
	 * Check if a path is absolute
	 * @param string $path
	 * @return bool
	 */
	public static function is_path_absolute($path)
	{
		return $path[0] === '/' || (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && isset($path[1]) && $path[1] === ':');
	}

	/**
	 * Get uuid v4
	 * @return string
	 */
	public static function get_uuid()
	{
		$uuid = Uuid::uuid4();
		return $uuid->toString();
	}

	public static function get_file_extension($file_name)
	{
		return pathinfo($file_name, PATHINFO_EXTENSION);
	}
}
