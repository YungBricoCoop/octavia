<?php

namespace ybc\octavia\Enums;

enum MiddlewareScopes: string
{
	case GLOBAL = "GLO";
	case GROUP = "GRP";
	case ROUTE = "RTE";
}
