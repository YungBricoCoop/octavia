<?php

namespace ybc\octavia\Router;

use ybc\octavia\Interfaces\RouteInterface;

class Route implements RouteInterface
{
	public $name = null;
	public $http_method = null;
	public $path = null;
	public $path_segments = null;
	public $dynamic_segments_values = [];
	public $func = null;
	public ?Upload $upload = null;
	public ?Query $query = null;
	public ?Body $body = null;
	public $is_upload = false;
	public $requires_login = false;
	public $requires_admin = false;

	public function __construct($name, $http_method, $path, $path_segments, $is_upload, $func)
	{
		$this->name = $name;
		$this->http_method = $http_method;
		$this->path = $path;
		$this->path_segments = $path_segments;
		$this->is_upload = $is_upload;
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

	public function __toString()
	{
		return $this->http_method . "_" . $this->func;
	}
}
