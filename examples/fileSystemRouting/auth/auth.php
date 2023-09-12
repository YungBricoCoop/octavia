<?php

$handler->get("/login", function ($q, $b) {
	return "Hi, this is the login page";
});

$handler->get("/register", function ($q, $b) {
	return "Hi, this is the register page";
});
