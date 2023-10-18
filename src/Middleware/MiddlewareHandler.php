<?php

namespace ybc\octavia\Middleware;

use ybc\octavia\Interfaces\{InputMiddlewareInterface, OutputMiddlewareInterface};
use ybc\octavia\Middleware\Output\{JsonEncode, HtmlEncode};
use ybc\octavia\{Request, Response};


class MiddlewareHandler
{
	/** @var InputMiddlewareInterface[] */
	private $input_middlewares = [];

	/** @var OutputMiddlewareInterface[] */
	private $output_middlewares = [];

	public function __construct($input_middlewares = [], $output_middlewares = [])
	{
		$this->input_middlewares = $input_middlewares;
		$this->output_middlewares = $output_middlewares;
	}

	/**
	 * Add a middleware to the stack.
	 * The stack will be executed in the order the middlewares were added.
	 * @param InputMiddlewareInterface|OutputMiddlewareInterface $middleware
	 * @return $this
	 */
	public function add($middleware)
	{
		if ($middleware instanceof OutputMiddlewareInterface) {
			$this->output_middlewares[] = $middleware;
			return $this;
		}

		// Default to input middleware
		$this->input_middlewares[] = $middleware;
		return $this;
	}

	/**
	 * Add a middleware to the beginning of the stack.
	 * @param InputMiddlewareInterface|OutputMiddlewareInterface $middleware
	 * @return $this
	 */
	public function add_before($middleware)
	{
		if ($middleware instanceof OutputMiddlewareInterface) {
			array_unshift($this->output_middlewares, $middleware);
			return $this;
		}

		// Default to input middleware
		array_unshift($this->input_middlewares, $middleware);
		return $this;
	}

	/**
	 * Add multiple middlewares to the stack.
	 * The stack will be executed in the order the middlewares were added.
	 * @param InputMiddlewareInterface[]|OutputMiddlewareInterface[] $middlewares
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
		$middlewares = $this->input_middlewares;
		foreach ($middlewares as $middleware) {
			$request = $middleware->handle($request);
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
		$middlewares = $this->output_middlewares;
		foreach ($middlewares as $middleware) {
			if ($return_html && $middleware instanceof JsonEncode) {
				$htmlMiddleware = new HtmlEncode();
				$response = $htmlMiddleware->handle($response);
				continue;
			}

			$response = $middleware->handle($response);
		}

		return $response;
	}
}
