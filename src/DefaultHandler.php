<?php
declare(strict_types=1);


namespace MNC\Router;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
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
            return new Response(404, [
                'content-type' => 'text/plain;charset=utf-8'
            ], sprintf('Cannot %s %s', $request->getMethod(), $request->getUri()->getPath()));
        });
    }
}