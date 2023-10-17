<?php

namespace ybc\octavia\Router;

use ybc\octavia\Utils\Utils;
use ybc\octavia\{MissingQueryParameterException, WrongQueryParameterTypeException};

class Query
{
	private mixed $data;
	private mixed $required_params;

	public function __construct(mixed $data = [], mixed $required_params = [])
	{
		$this->data = $data;
		$this->required_params = $required_params;
	}


	/**
	 * Validate all the query params
	 * @throws MissingQueryParameterException
	 * @throws WrongQueryParameterTypeException
	 * @return bool
	 */
	public function validate()
	{
		if (!$this->required_params || empty($this->required_params)) return true;

		$this->validate_proprety($this->data, $this->required_params);
	}

	private function validate_proprety(mixed $data, mixed $required)
	{
		// If the value is a string and represents a class
		if (is_string($required) && class_exists($required)) {
			$result = Utils::validate($data, $required);
			if ($result["is_missing"]) {
				throw new MissingQueryParameterException($result["property"]);
			}
			if ($result["is_wrong_type"]) {
				throw new WrongQueryParameterTypeException($result["property"] . ", expected {" . $result["expected_type"] . "} but got {" . $result["actual_type"] . "}");
			}
			return true;
		}

		foreach ($required as $key) {
			// If the key does not exist in the data array, throw an exception
			if (!array_key_exists($key, $data)) {
				throw new MissingQueryParameterException($key);
			}
		}
		return true;
	}


	/**
	 * Get a query param
	 * @param string $key The key of the param
	 * @return mixed The value of the param, or null if it does not exist
	 */
	public function __get(string $key)
	{
		return $this->data[$key] ?? null;
	}

	/**
	 * Set the input query data
	 * @param mixed $data
	 * @return void
	 */
	public function set_data(mixed $data)
	{
		$this->data = $data;
	}

	/**
	 * Set the required params
	 * @param mixed $required_params
	 * @return void
	 */
	public function set_required_params(mixed $required_params)
	{
		$this->required_params = $required_params;
	}
}
