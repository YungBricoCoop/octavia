<?php

namespace ybc\octavia\Middleware;

#[\Attribute(\Attribute::TARGET_CLASS)]
class MiddlewareIdentifier
{
    public function __construct(
        public string $identifier
    ) {}
}
