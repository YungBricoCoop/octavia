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

			if (!self::validate($object->$proprety_name, $expected_type)) {
				$result["is_wrong_type"] = true;
				$result["expected_type"] = $expected_type;
				$result["actual_type"] = $actualType;
				return $result;
			}

			continue;
		}

		return $result;
	}
}
