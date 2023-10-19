<?php

namespace ybc\octavia\Middleware;

use ybc\octavia\Middleware\Output\{JsonEncode, HtmlEncode};
use ybc\octavia\Enums\MiddlewareStages;
use ybc\octavia\Router\Route;

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
		//INFO: This implemention quit the chain if a middleware sets the terminate_chain property to true.
		// But working with global and route middlewares is quite complex and I'm not sure if this is the best way to do it.

		//FIXME: The order of processing the middlewares is not correct. 
		// If we have a custom Html() middleware and a global Json() middleware, the Json() middleware will be executed first.
		$context = $this->process_middlewares($this->middlewares[$stage->value], $context);

		if (!$context->terminate_chain && $context->route && isset($context->route->middlewares[$stage->value])) {
			$context = $this->process_middlewares($context->route->middlewares[$stage->value], $context);
		}

		return $context;
	}

	private function process_middlewares(array $middlewares, Context $context): Context
	{
		foreach ($middlewares as $middleware) {
			$context = $middleware->handle($context);

			if ($middleware->terminate_chain) {
				$context->terminate_chain = true;
				break;
			}
		}

		return $context;
	}
}
