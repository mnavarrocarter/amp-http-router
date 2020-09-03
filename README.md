AMP HTTP Router
===============

A high performance routing engine that handles requests for an Amp HTTP Server inspired in
Express JS.

## Installation

```bash
composer require mnavarrocarter/amp-http-router
```
 
## Quick Start

```php
<?php
declare(strict_types=1);

use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use MNC\Router\Router;
use MNC\Router\RoutingContext;
use function MNC\Router\handleFunc;
use function MNC\Router\html;
use function MNC\Router\listenAndServe;

function homepage(): Response {
    return html('Hello world!');
}

function findUser(Request $request): Response {
    $id = RoutingContext::of($request)->getParam('id');
    return html(sprintf('The user id is %s', $id));
}

$router = Router::create();
$router->get('/', handleFunc('homepage'));
$router->get('/users/:id', handleFunc('findUser'));

Amp\Loop::run(fn() => yield listenAndServe('0.0.0.0:8000', $router));
```

### Router Composition

You can have multiple routers mounted to different paths for more efficient
routing:

```php
<?php

use MNC\Router\Router;

$app = Router::create();
// Define main app routes

$api = Router::create();
// Define api routes

// Api routes will be under `/api`
$app->mount('/api', $api);
```