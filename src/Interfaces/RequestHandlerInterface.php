<?php

namespace ybc\octavia\Interfaces;

use ybc\octavia\Router\Route;

interface RequestHandlerInterface
{
	public function get(string $path, callable $callback): Route;
	public function post(string $path, callable $callback): Route;
	public function put(string $path, callable $callback): Route;
	public function delete(string $path, callable $callback): Route;
	public function patch(string $path, callable $callback): Route;
	public function options(string $path, callable $callback): Route;
	public function head(string $path, callable $callback): Route;
	public function upload(string $path, callable $callback, bool $allow_multiple_files = true, array $allowed_extensions = [], string $max_size = null) : Route;
	public function health(string $path, callable $callback, bool $auth_required = false): Route;
	public function handle_request();
}
