<?php

namespace ybc\octavia\Utils;

use Ramsey\Uuid\Uuid;
use Dotenv\Dotenv;

class Utils
{
	/**
	 * Validate an object by using a class
	 * @param mixed $object The object to validate
	 * @param class $class The class to validate against
	 * @return array [string $property, bool $is_missing, bool $is_wrong_type, string $expected_type, string $actual_type]
	 */
	public static function validate(mixed $object, string $className)
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
	public static function get_route_name(string $path)
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
	public static function get_route_path_segments(string $path)
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

	public static function get_route_dynamic_path_segments_types(array $segments)
	{
		// types can only be int or string ex {user:int}
		$types = [];
		foreach ($segments as $segment) {
			if (substr($segment, 0, 1) !== "{" || !str_contains($segment, ":")) {
				continue;
			}

			$segment = substr($segment, 1, -1);
			$segment = explode(":", $segment);
			$type = $segment[1];
			if ($type !== "int" && $type !== "string") {
				continue;
			}
			$types[] = $type;
		}
		return empty($types) ? null : $types;
	}

	/**
	 * Validate a single dynamic param
	 * @param mixed $param
	 * @param string $type
	 * @return array [string $param, bool $is_valid, string $expected_type, string $actual_type]
	 */
	public static function validate_dynamic_param(mixed $param, string $type)
	{
		$result = [
			"param" => $param,
			"is_valid" => true,
			"expected_type" => $type,
			"actual_type" => gettype($param),
		];

		if ($type !== "int" && $type !== "string") {
			return $result;
		}

		if ($type === "int" && !is_numeric($param)) {
			$result["is_valid"] = false;
			return $result;
		}

		if (($type === "string" && !is_string($param)) || ($type === "string" && is_numeric($param))) {
			$result["is_valid"] = false;
			return $result;
		}

		if ($type === "int" && is_numeric($param)) {
			$param = (int) $param;
			$result["param"] = $param;
			$result["actual_type"] = gettype($param);
			return $result;
		}

		return $result;
	}

	/**
	 * Check if a path is absolute
	 * @param string $path
	 * @return bool
	 */
	public static function is_path_absolute(string $path)
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

	/**
	 * Get the extension of a file without the dot
	 * @param string $file_name
	 * @return string
	 */
	public static function get_file_extension(string $file_name)
	{
		return pathinfo($file_name, PATHINFO_EXTENSION);
	}

	/**
	 * Get the path of the file that called this function
	 * @param int $level
	 * @return string The path of the file
	 */
	public static function get_path_from_backtrace(int $level = 0)
	{
		$backtrace = debug_backtrace();
		$path = $backtrace[$level]['file'];
		return $path;
	}

	/**
	 * Get the folder difference between two paths
	 * @param string $base_path
	 * @param string $path
	 * @return string The folder difference or empty string if there is no difference
	 */
	public static function extract_folder_diff(string $base_path, string $path)
	{
		$remaining_path = str_replace(dirname($base_path), '', $path);
		$remaining_path = trim($remaining_path, DIRECTORY_SEPARATOR);
		$remaining_path = str_replace('\\', '/', $remaining_path);
		$remaining_path = dirname($remaining_path);
		if ($remaining_path == '.') return '';
		return '/' . $remaining_path;
	}

	/**
	 * Convert a size string to bytes
	 * @param string $size_str
	 * @return int
	 */
	public static function convert_to_bytes(string $size_str)
	{
		if (!$size_str) return 0;
		$size_str = strtolower($size_str);
		if (str_contains($size_str, 'b')) $size_str = str_replace('b', '', $size_str);
		$prefix = substr($size_str, -1);
		switch ($prefix) {
			case 'M':
			case 'm':
				return (int)$size_str * 1048576;
			case 'K':
			case 'k':
				return (int)$size_str * 1024;
			case 'G':
			case 'g':
				return (int)$size_str * 1073741824;
			default:
				return 0;
		}
	}

	/**
	 * Validate basic auth
	 * @param string $username
	 * @param string $password
	 * @return bool True if valid
	 */
	public static function validate_basic_auth(string $username, string $password)
	{
		// Check if the PHP_AUTH_USER and PHP_AUTH_PW server variables are set
		if (!isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
			return false;
		}

		// Compare the provided username and password to the ones from the HTTP request
		return $_SERVER['PHP_AUTH_USER'] === $username && $_SERVER['PHP_AUTH_PW'] === $password;
	}

	/**
	 * Load env file
	 * @param string $env_file
	 * @return void
	 */
	public static function load_env(string $env_file): bool
	{
		$dir = dirname($env_file);
		$file = basename($env_file);
		try {
			$dotenv = Dotenv::createImmutable($dir);
			$dotenv->load($file);
			return true;
		} catch (\Exception $e) {
			return false;
		}
	}
}
