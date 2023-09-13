# Octavia

**TLDR** : Octavia is a sleek and efficient **PHP** framework designed to simplify the creation of APIs.

> Inspired by the simplicity of [FastAPI](https://fastapi.tiangolo.com/), Octavia provides a straightforward and intuitive approach to API development, making it accessible for beginners while still powerful enough for experienced developers.

<p align="center">
  <img src="assets/logo.svg" alt="LOGO_ALT_TEXT" width="140px">
</p>

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
