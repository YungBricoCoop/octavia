# Octavia

<p align="center" style="margin-top: 30px; margin-bottom: 30px;">
  <img src="assets/logo.svg" alt="LOGO_ALT_TEXT" width="140px">
</p>

**TLDR** : Octavia is a sleek and efficient **PHP** framework designed to simplify the creation of APIs.

> Inspired by the simplicity of [FastAPI](https://fastapi.tiangolo.com/), Octavia provides a straightforward and intuitive approach to API development, making it accessible for beginners while still powerful enough for experienced developers.

## ⭐ Features

-   File system based routing
-   Dynamic path parameters with type validation
-   Request body and query parameters validation
-   File upload
-   Custom exceptions
-   Custom logger
-   Middleware support
-   Template engine
-   Session manager

## 🚀 Quickstart

## 🤖 How does it work?

The framework is based on a `RequestHandler` object that will register the routes and handle the incoming requests.
You can register a route by calling the `get`, `post`, `put`, `patch`, `delete`, `options`, `head`, `upload` methods of the `RequestHandler` object. 

### Callback parameters order

1. **Path Parameters**: Captured from the route (if any)
2. **Query (`$q`)**: Parameters after the ? in the URL.
3. **Body (`$b`)**: Data from the request body.
4. **Session (`$s`)**: Session variables.
5. **Files(`$f`)**: Array of uploaded files (if any).

Callbacks will always be called with a minimum of 3 parameters: `$q`, `$b`, `$s`.

**Example**:  /say_hello/{name:string}?age=20

```php
($name, $q, $b, $s) => {return "Hello " . $name . " you are " . $q->age . " years old";}
```


### Middleware

By default the `JsonMiddleware` is used to parse the request body and set the response with the correct headers. The middlewares have an `handle_before` and `handle_after` method that will be called before and after the route function.

## 📋 Examples

Here is a simple example of an Octavia application:

```php
<?php

require '../vendor/autoload.php';

use ybc\octavia\RequestHandler;

$handler = new RequestHandler();

$handler->get("/hello", function ($q, $b) {
	return "Hello world";
});

$handler->post("/say_hello/{name:string}", function ($name, $q, $b) {
	return "Hello " . $name;
});

$handler->handle_request();
```

Here is more examples:

-   [Get started](examples/getStarted.php) - A simple example showcasing the basic features of Octavia (Query and body parameters validation).
-   [File system based routing](examples/fileSystemRouting) - Showcase how to use file system based routing.
-   [Session](examples/session) - Showcase how to use session manager (Basic user login/logout).
-   [Upload](examples/upload) - Showcase how to upload files.

## 📝 Todo

-   [x] Use enum for the `http method`
-   [x] Dynamic routing
-   [x] Pass dynamic params to the route function
-   [x] Validate dynamic params type
-   [x] File upload
-   [x] Throw and handle custom exceptions
-   [x] Validate request Query params and Body by Class, array of Class, or array of strings, accept optional params
-   [x] Custom Logger
-   [x] Middleware support
-   [x] File system based routing
-   [x] Template engine
-   [x] Session manager
-   [ ] Improve security

## 📈 Stats
[![wakatime](https://wakatime.com/badge/user/ee872f10-6167-41c6-8aad-e80d7519df4c/project/dfd2622d-4d56-45f0-bdff-1c14002e441a.svg?style=for-the-badge)](https://wakatime.com/badge/user/ee872f10-6167-41c6-8aad-e80d7519df4c/project/dfd2622d-4d56-45f0-bdff-1c14002e441a)