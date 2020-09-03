<?php
declare(strict_types=1);


namespace MNC\Router;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Status;
use Amp\Promise;
use function Amp\call;

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
        return call(static function () use ($request) {
            $status = Status::NOT_FOUND;
            if (RoutingContext::of($request)->isMethodNotAllowed()) {
                $status = Status::METHOD_NOT_ALLOWED;
            }

            return new Response($status, [
                'content-type' => 'text/plain;charset=utf-8'
            ], sprintf('Cannot %s %s', $request->getMethod(), $request->getUri()->getPath()));
        });
    }
}