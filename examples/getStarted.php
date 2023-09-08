<?php

require '../vendor/autoload.php';

use Ybc\Octavia\RequestHandler;
use Ybc\Octavia\Response;


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

$handler->get("/hello/{name}", function ($name, $q, $b) {
	return "Hello $name";
});

$handler->post("/register", function ($q, $b) {
	return "User $b->username registered successfully, using $q->language language";
})->query(["language"])->body(RegisterUser::class);

$handler->post("/login", function ($q, $b) {
	if ($b->username == "admin" && $b->password == "admin") {
		return "Login Successful";
	}
	return new Response("Login Failed", 401);
})->body(LoginUser::class);


$handler->handle_request();
