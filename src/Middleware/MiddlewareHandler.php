<?php

namespace ybc\octavia\Middleware;

use ybc\octavia\Middleware\Output\{JsonEncode, HtmlEncode};
use ybc\octavia\Enums\MiddlewareStages;

class MiddlewareHandler
{
	private $middlewares = [
		MiddlewareStages::BEFORE_ROUTING->value => [],
		MiddlewareStages::AFTER_ROUTING->value => [],
		MiddlewareStages::BEFORE_OUTPUT->value => [],
	];

	/**
	 * Add a middleware to the stack.
	 * The stack will be executed in the order the middlewares were added.
	 * @param Middleware $middleware
	 * @return $this
	 */
	public function add(Middleware $middleware)
	{
		$this->middlewares[$middleware->stage->value][] = $middleware;
		return $this;
	}

	/**
	 * Add a middleware to the beginning of the stack.
	 * @param Middleware $middleware
	 * @return $this
	 */
	public function add_before(Middleware $middleware)
	{
		array_unshift($this->middlewares[$middleware->stage->value], $middleware);
		return $this;
	}

	/**
	 * Add multiple middlewares to the stack.
	 * The stack will be executed in the order the middlewares were added.
	 * @param array $middlewares
	 * @return $this
	 */
	public function add_many(array $middlewares)
	{
		foreach ($middlewares as $middleware) {
			$this->add($middleware);
		}
		return $this;
	}

	/**
	 * Handle the request through the middleware stack.
	 * @param MiddlewareStages $stage, the stage of the middleware stack to handle
	 * @param Context $context
	 * @return Context
	 */
	public function handle(MiddlewareStages $stage, Context $context)
	{
		$middlewares = $this->middlewares[$stage->value];
		foreach ($middlewares as $middleware) {
			if ($context->route->return_html && $middleware instanceof JsonEncode) {
				$html_middleware = new HtmlEncode();
				$context = $html_middleware->handle($context);
				continue;
			}

			$context = $middleware->handle($context);
		}

		return $context;
	}
}
