<?php
declare(strict_types=1);


namespace MNC\Router;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Status;
use Amp\Promise;
use Amp\Success;

/**
 * Class DefaultHandler
 * @package MNC\Router
 * @internal
 */
class DefaultHandler implements RequestHandler
{
    /**
     * @param Request $request
     * @return Promise<Response>
     */
    public function handleRequest(Request $request): Promise
    {
        $status = Status::NOT_FOUND;
        if (RoutingContext::of($request)->isMethodNotAllowed()) {
            $status = Status::METHOD_NOT_ALLOWED;
        }

        $response =  new Response($status, [
            'content-type' => 'text/plain;charset=utf-8'
        ], sprintf('Cannot %s %s', $request->getMethod(), $request->getUri()->getPath()));

        return new Success($response);
    }
}