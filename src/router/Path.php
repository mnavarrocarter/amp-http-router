<?php
declare(strict_types=1);

namespace MNC\Router;

use Amp\Coroutine;
use Amp\Http\Server\HttpServer;
use Amp\Http\Server\Middleware;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\ServerObserver;
use Amp\Promise;
use Amp\Success;
use Generator;
use MNC\PathToRegExpPHP\NoMatchException;
use MNC\PathToRegExpPHP\PathRegExp;
use MNC\PathToRegExpPHP\PathRegExpFactory;

/**
 * Class Path
 * @package MNC\Router
 */
class Path implements Middleware, ServerObserver
{
    protected PathRegExp $path;
    protected RequestHandler $handler;

    /**
     * @param string $path
     * @param RequestHandler $handler
     * @return Path
     */
    public static function fromString(string $path, RequestHandler $handler): Path
    {
        return new self(PathRegExpFactory::create($path, 0), $handler);
    }

    /**
     * Route constructor.
     *
     * @param PathRegExp $path
     * @param RequestHandler $handler
     */
    public function __construct(PathRegExp $path, RequestHandler $handler)
    {
        $this->path = $path;
        $this->handler = $handler;
    }

    /**
     * @param Request $request
     * @param RequestHandler $next
     *
     * @return Promise
     */
    public function handleRequest(Request $request, RequestHandler $next): Promise
    {
        return new Coroutine($this->checkPath($request, $next));
    }

    public function onStop(HttpServer $server): Promise
    {
        if ($this->handler instanceof ServerObserver) {
            return $this->handler->onStop($server);
        }
        return new Success();
    }

    public function onStart(HttpServer $server): Promise
    {
        if ($this->handler instanceof ServerObserver) {
            return $this->handler->onStart($server);
        }
        return new Success();
    }

    /**
     * @param Request $request
     * @param RequestHandler $next
     * @return Promise<Response|null>
     */
    protected function postMatchingHook(Request $request, RequestHandler $next): Promise
    {
        return new Success();
    }

    /**
     * @param Request $request
     * @param RequestHandler $next
     * @return Generator
     */
    private function checkPath(Request $request, RequestHandler $next): Generator
    {
        // We get the routing uri to match
        $uri = RoutingContext::of($request)->getUriToMatch();

        // We fix the trailing slash if missing
        if (substr($uri->getPath(), -1) !== '/') {
            $uri = $uri->withPath($uri->getPath().'/');
            $request->setUri($uri);
        }

        $path = $uri->getPath();

        // We try to match the path
        try {
            $result = $this->path->match($path);
        } catch (NoMatchException $e) {
            return yield $next->handleRequest($request);
        }

        $response = yield $this->postMatchingHook($request, $next);

        if ($response instanceof Response) {
            return $response;
        }

        // If it matches, we create a new path in the request
        $newPath = str_replace($result->getMatchedString(), '', $uri->getPath());
        RoutingContext::of($request)->setUriToMatch($uri->withPath($newPath));
        RoutingContext::of($request)->setLastMatchResult($result);

        return yield $this->handler->handleRequest($request);
    }
}