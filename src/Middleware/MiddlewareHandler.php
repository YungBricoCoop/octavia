<?php

namespace ybc\octavia\Middleware;

use ybc\octavia\Enums\MiddlewareStages;
use ybc\octavia\Utils\Utils;

class MiddlewareHandler
{
	private $middlewares = [];

	/**
	 * Add one or multiple middlewares to the stack.
	 * The stack will be executed in the order the middlewares were added.
	 * @param Middleware|Middleware[] $middleware
	 * @return $this
	 */
	public function add($middleware)
	{
		if (!is_array($middleware)) {
			$this->middlewares[$middleware->stage->value][] = $middleware;
			return $this;
		}

		foreach ($middleware as $m) {
			$this->middlewares[$m->stage->value][] = $m;
		}
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
	 * Handle the request through the middleware stack.
	 * @param MiddlewareStages $stage, the stage of the middleware stack to handle
	 * @param Context $context
	 * @param Middleware[] $group_middlewares
	 * @param Middleware[] $excluded_global_middlewares
	 * @param Middleware[] $route_middlewares
	 * @param Middleware[] $excluded_group_route_middlewares
	 * @return Context
	 */
	public function handle(MiddlewareStages $stage, Context $context, $group_middlewares = [], $excluded_global_middlewares = [], $route_middlewares = [], $excluded_group_route_middlewares = [])
	{
		$global_stage_middlewares = $this->middlewares[$stage->value] ?? [];
		$group_stage_middlewares = $group_middlewares[$stage->value] ?? [];
		$route_stage_middlewares = $route_middlewares[$stage->value] ?? [];

		$combined_global_exclusions = array_merge($excluded_global_middlewares, $excluded_group_route_middlewares);

		$global_stage_middlewares = Utils::exclude_middlewares($global_stage_middlewares, $combined_global_exclusions);
		$group_stage_middlewares = Utils::exclude_middlewares($group_stage_middlewares, $excluded_group_route_middlewares);
		$route_stage_middlewares = Utils::exclude_middlewares($route_stage_middlewares, $excluded_group_route_middlewares);
	
		$middleware_stack = array_merge($global_stage_middlewares, $group_stage_middlewares, $route_stage_middlewares);

		foreach ($middleware_stack as $middleware) {
			if (!($middleware instanceof Middleware)) {
				continue;
			}

			$context = $middleware->handle($context);

			if ($middleware->terminate_chain) {
				break;
			}
		}

		return $context;
	}
}
