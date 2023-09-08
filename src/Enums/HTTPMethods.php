<?php

namespace Ybc\Octavia\Enums;

enum HTTPMethods: string
{
	case GET = "GET";
	case POST = "POST";
	case PUT = "PUT";
	case DELETE = "DELETE";
	case OPTIONS = "OPTIONS";
	case HEAD = "HEAD";
	case PATCH = "PATCH";
}
