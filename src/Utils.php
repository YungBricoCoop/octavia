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
	 * @return bool
	 */
	public static function validate($object, string $className)
	{
		$object = (object) $object;
		$className = str_starts_with($className, "?") ? substr($className, 1) : $className;

		if (!class_exists($className)) {
			throw new \InvalidArgumentException("UNKNOWN_CLASS $className");
			return false;
		}

		$reflection = new \ReflectionClass($className);
		$properties = $reflection->getProperties();

		// Parse all class properties
		foreach ($properties as $property) {
			$propertyName = $property->getName();
			$type = $property->getType();

			if (!property_exists($object, $propertyName)) {
				return false;
			}


			if ($type->allowsNull() && $object->$propertyName === null) {
				continue;
			}

			// Skip if no type is set
			if (!$type) continue;

			$expectedType = (string) $type;

			if ($type->isBuiltin()) {
				if ($expectedType === 'int' || $expectedType === '?int') {
					$expectedType = 'integer';
				}
				if ($expectedType === 'bool' || $expectedType === '?bool') {
					$expectedType = 'boolean';
				}
				if ($expectedType === 'float' || $expectedType === '?float') {
					$expectedType = 'double';
				}
				if ($expectedType !== gettype($object->$propertyName)) {
					print_r($expectedType);
					print_r(gettype($object->$propertyName));
					return false;
				}
				continue;
			}

			if (!class_exists($className)) {
				throw new \InvalidArgumentException("UNKNOWN_CLASS $className");
				return false;
			}

			if (!self::validate($object->$propertyName, $expectedType)) {
				return false;
			}

			continue;
		}
		return true;
	}
}
