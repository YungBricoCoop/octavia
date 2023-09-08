<?php

namespace Ybc\Octavia\Interfaces;

interface RouteInterface
{
	public function login(): self;
	public function admin(): self;

	/** 
	 * Set the required query params
	 * A class can be used like a schema, type hinting will be used to validate the query params, use nullable types to allow optional params
	 * Ex 1: $route->query(User::class);
	 * Ex 2: $route->query(["user" => User::class, "password"]);
	 * @param  class|string[] $params  
	 */
	public function query($params): self;

	/** 
	 * Set the required query params
	 * A class can be used like a schema, type hinting will be used to validate the query params, use nullable types to allow optional params
	 * Ex 1: $route->query(User::class);
	 * Ex 2: $route->query(["user" => User::class, "password"]);
	 * @param  class|string[] $params  
	 */
	public function q($params): self;

	/** 
	 * Set the required body params
	 * Classes can be used like a schema, type hinting will be used to validate the body params, use nullable types to allow optional params
	 * Ex 1: $route->body(User::class);
	 * Ex 2: $route->body(["user" => User::class, "password"]);
	 * Ex 3: $route->body(["user_id", "password"]);
	 * @param  class|class[]|string[] $params 
	 */
	public function body($body_params): self;

	/** 
	 * Set the required body params
	 * Classes can be used like a schema, type hinting will be used to validate the body params, use nullable types to allow optional params
	 * Ex 1: $route->body(User::class);
	 * Ex 2: $route->body(["user" => User::class, "password"]);
	 * Ex 3: $route->body(["user_id", "password"]);
	 * @param  class|class[]|string[] $params  
	 */
	public function b($params): self;

	/**
	 * Set the callback function
	 * @param function $func 
	 */
	public function f($func): self;
}
