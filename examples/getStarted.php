<?php

require '../vendor/autoload.php';

use ybc\octavia\RequestHandler;
use ybc\octavia\Response;

/**
 * ROUTES:
 * GET /api/v1/hello/{name}
 * POST /api/v1/register
 * POST /api/v1/login
 */

class RegisterUser
{
	public string $username;
	public string $password;
	public string $firstname;
	public string $lastname;
}

class LoginUser
{
	public string $username;
	public string $password;
}

$handler = new RequestHandler();
$handler->prefix("/api/v1");

$group = $handler->group();

$group->get("/hello/{name}", function ($name, $q, $b) {
	return "Hello $name";
});

$group->get("/hello/{name}/html", function ($name, $q, $b) {
	return "<html><body><h1>Hello $name</h1></body></html>";
});

$group->post("/register", function ($q, $b) {
	return "User $b->username registered successfully, using $q->language language";
})->query(["language"])->body(RegisterUser::class);

$group->post("/login", function ($q, $b) {
	if ($b->username == "admin" && $b->password == "admin") {
		return "Login Successful";
	}
	return new Response("Login Failed", 401);
})->body(LoginUser::class);


$handler->handle_request();
