<?php

$group = $handler->group("/auth");

$group->get("/login", function ($q, $b) {
	return "Hi, this is the login page";
});

$group->get("/register", function ($q, $b) {
	return "Hi, this is the register page";
});
