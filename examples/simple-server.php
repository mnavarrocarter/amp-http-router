<?php
declare(strict_types=1);

use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use MNC\Router\Router;
use MNC\Router\RoutingContext;
use function MNC\Router\handleFunc;
use function MNC\Router\html;
use function MNC\Router\listenAndServe;

require_once __DIR__ . '/../vendor/autoload.php';

function homepage(Request $request): Response {
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