<?php

namespace ybc\octavia\Router\RouteTypes;

use ybc\octavia\Router\Route;

class Upload extends RouteType
{
	public static $http_method = "POST";
	private string $upload_dir;
	private bool $allow_multiple_files;
	private array $allowed_extensions;
	private string $max_size;

	public function __construct(string $upload_dir, bool $allow_multiple_files, array $allowed_extensions, string $max_size)
	{
		$this->upload_dir = $upload_dir;
		$this->allow_multiple_files = $allow_multiple_files;
		$this->allowed_extensions = $allowed_extensions;
		$this->max_size = $max_size;
	}
	
	public function handle(Route $route)
	{
		$route->upload->set_params($this->upload_dir, $this->allow_multiple_files, $this->allowed_extensions, $this->max_size);
		$route->upload->validate();
		$route->upload->upload();
	}
	
	public function get_callback_params(Route $route)
	{
		return $route->upload->get_uploaded_files();
	}
}
