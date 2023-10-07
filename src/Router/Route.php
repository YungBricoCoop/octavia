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
	public $name = null;
	public ?RouteType $type = null;
	public $path = null;
	public $path_segments = null;
	public $dynamic_segments_types = null;
	public $dynamic_segments_values = [];
	public $func = null;
	public ?Upload $upload = null;
	public ?Query $query = null;
	public ?Body $body = null;
	public $is_upload = false;
	public $is_health = false;
	public $requires_login = false;
	public $requires_admin = false;

	public function __construct($name, $type, $path, $path_segments, $dynamic_segments_types, $is_upload, $is_health, $func)
	{
		$this->name = $name;
		$this->type = $type;
		$this->path = $path;
		$this->path_segments = $path_segments;
		$this->dynamic_segments_types = $dynamic_segments_types;
		$this->is_upload = $is_upload;
		$this->is_health = $is_health;
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

	public function handle(){
		$this->type->handle($this);
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
		if ($this->is_upload) $this->upload->validate();
	}

	public function __toString()
	{
		return $this->type . "_" . $this->func;
	}
}
