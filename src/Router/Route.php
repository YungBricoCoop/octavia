<?php

namespace ybc\octavia\Router;

use ybc\octavia\Utils\Utils;
use ybc\octavia\Interfaces\RouteInterface;
use ybc\octavia\Router\RouteTypes\RouteType;

use WrongPathParameterTypeException;
use MissingBodyParameterException;
use MissingQueryParameterException;
use WrongQueryParameterTypeException;
use MissingObjectPropertyException;
use WrongObjectPropertyTypeException;

class Route implements RouteInterface
{
	public string $name;
	public RouteType $type;
	public string $path;
	public array $path_segments;
	public array $dynamic_segments_types;
	public array $dynamic_segments_values;
	public mixed $func;
	public ?Upload $upload;
	public ?Query $query;
	public ?Body $body;
	public bool $requires_login;
	public bool $requires_admin;

	public function __construct($name, $type, $path, $path_segments, $dynamic_segments_types, $func)
	{
		$this->name = $name;
		$this->type = $type;
		$this->path = $path;
		$this->path_segments = $path_segments ?? [];
		$this->dynamic_segments_types = $dynamic_segments_types ?? [];
		$this->dynamic_segments_values = [];
		$this->requires_login = false;
		$this->requires_admin = false;
		$this->func = $func;

		$this->query = new Query();
		$this->body = new Body();
		$this->upload = new Upload();
	}

	public function login(): self
	{
		$this->requires_login = true;
		return $this;
	}

	public function admin(): self
	{
		$this->requires_admin = true;
		return $this;
	}

	public function query($params): self
	{
		$this->query->set_required_params($params);
		return $this;
	}

	public function q($params): self
	{
		$this->query->set_required_params($params);
		return $this;
	}

	public function body($params): self
	{
		$this->body->set_required_body($params);
		return $this;
	}

	public function b($params): self
	{
		$this->body->set_required_body($params);
		return $this;
	}

	public function f($func): self
	{
		$this->func = $func;
		return $this;
	}

	public function handle()
	{
		return $this->type->handle($this);
	}

	public function get_callback_params($session)
	{
		$function_params = $this->dynamic_segments_values;
		$function_params[] = $this->query;
		$function_params[] = $this->body;
		$function_params[] = $session;
		$type_params = $this->type->get_callback_params($this) ?? [];
		$function_params = array_merge($function_params, $type_params);

		return $function_params;
	}

	/**
	 * Validate the body, query and upload params
	 * @throws MissingBodyParameterException
	 * @throws MissingQueryParameterException
	 * @throws WrongQueryParameterTypeException
	 * @throws WrongPathParameterTypeException
	 * @throws MissingObjectPropertyException
	 * @throws WrongObjectPropertyTypeException
	 * @return void
	 */
	public function validate()
	{
		foreach ($this->dynamic_segments_types as $index => $type) {
			if (!isset($this->dynamic_segments_values[$index])) continue;
			$path_param = $this->dynamic_segments_values[$index];
			$result = Utils::validate_dynamic_param($path_param, $type);
			if (!$result["is_valid"]) {
				throw new WrongPathParameterTypeException($result["param"] . ", expected {" . $result["expected_type"] . "} but got {" . $result["actual_type"] . "}");
			}
			$this->dynamic_segments_values[$index] = $result["param"];
		}
		$this->query->validate();
		$this->body->validate();
	}

	public function __toString()
	{
		return $this->name . " " . $this->path;
	}
}
