<?php

namespace ybc\octavia\Middleware;

use ybc\octavia\Interfaces\MiddlewareInterface;
use ybc\octavia\Request;
use ybc\octavia\Response;


class MiddlewareHandler
{
	/** @var MiddlewareInterface[] */
	private $middlewares = [];

	public function __construct($middlewares = [])
	{
		$this->middlewares = $middlewares;
	}

	/**
	 * Add a middleware to the stack.
	 * The stack will be executed in the order the middlewares were added.
	 * @param MiddlewareInterface $middleware
	 * @return $this
	 */
	public function add(MiddlewareInterface $middleware)
	{
		$this->middlewares[] = $middleware;
		return $this;
	}

	/**
	 * Add a middleware to the beginning of the stack.
	 * @param MiddlewareInterface $middleware
	 * @return $this
	 */
	public function add_before(MiddlewareInterface $middleware)
	{
		array_unshift($this->middlewares, $middleware);
		return $this;
	}

	/**
	 * Add multiple middlewares to the stack.
	 * The stack will be executed in the order the middlewares were added.
	 * @param MiddlewareInterface[] $middlewares
	 * @return $this
	 */
	public function add_many($middlewares)
	{
		foreach ($middlewares as $middleware) {
			$this->add($middleware);
		}
		return $this;
	}

	/**
	 * Pass the request through the middleware stack.
	 * @param Request $request
	 * @return Request
	 */
	public function handle_before(Request $request)
	{
		$middlewares = $this->middlewares;
		foreach ($middlewares as $middleware) {
			$request = $middleware->handle_before($request);
		}

		return $request;
	}

	/**
	 * Pass the response through the middleware stack.
	 * @param Response $response
	 * @param bool $return_html
	 * @return Response
	 */
	public function handle_after(Response $response, bool $return_html = false)
	{
		$middlewares = $this->middlewares;
		foreach ($middlewares as $middleware) {
			if ($return_html && $middleware instanceof JsonMiddleware) {
				$htmlMiddleware = new HtmlMiddleware();
				$response = $htmlMiddleware->handle_after($response);
				continue;
			}

			$response = $middleware->handle_after($response);
		}

		return $response;
	}
}
