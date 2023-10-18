<?php

namespace ybc\octavia\Enums;

enum MiddlewareStages : string
{
	// INPUT
	case BEFORE_ROUTING = "BFR";
	case AFTER_ROUTING = "AFR";
	
	// OUTPUT
	case BEFORE_OUTPUT = "BFO";
}
