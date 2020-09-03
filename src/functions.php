<?php
declare(strict_types=1);

namespace MNC\Router;

use Amp\Http\Server\HttpServer;
use Amp\Http\Server\Options;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Status;
use Amp\Promise;
use Amp\Socket\Server as Socket;
use JsonException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use function Amp\call;

/**
 * @param string $address
 * @param RequestHandler $handler
 * @param LoggerInterface|null $logger
 * @param Options|null $options
 * @return Promise
 */
function listenAndServe(string $address, RequestHandler $handler, LoggerInterface $logger = null, Options $options = null): Promise
{
    return call(static function () use ($address, $handler, $logger, $options) {
        $sockets = [
            Socket::listen($address)
        ];

        $logger = $logger ?? new NullLogger();

        $server = new HttpServer($sockets, $handler, $logger, $options);

        yield $server->start();

        return $server;
    });
}

/**
 * @param callable $callable
 * @return RequestHandler
 */
function handleFunc(callable $callable): RequestHandler {
    return new RequestHandler\CallableRequestHandler($callable);
}

function html($content, int $status = Status::OK): Response {
    return new Response($status, [
        'Content-Type' => 'text/html; charset=utf-8'
    ], $content);
}

/**
 * @param $content
 * @param int $status
 * @return Response
 * @throws JsonException
 */
function json($content, int $status = Status::OK): Response {
    return new Response($status, [
        'Content-Type' => 'application/json; charset=utf-8'
    ], json_encode($content, JSON_THROW_ON_ERROR));
}