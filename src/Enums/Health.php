<?php

namespace ybc\octavia\Enums;

enum Health: string
{
	case HEALTHY = "HEALTHY";
	case CRITICAL = "CRITICAL";
	case DEGRADED = "DEGRADED";
	case UNKNOWN = "UNKNOWN";
	case MAINTENANCE = "MAINTENANCE";
	case OFFLINE = "OFFLINE";
}
