<?php
declare(strict_types=1);

namespace MNC\Router;

use Amp\Http\Server\Middleware;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\RequestHandler as Next;
use Amp\Http\Server\Response;
use Amp\Promise;
use MNC\PathToRegExpPHP\NoMatchException;
use MNC\PathToRegExpPHP\PathRegExp;
use MNC\PathToRegExpPHP\PathRegExpFactory;
use function Amp\call;

/**
 * Class Path
 * @package MNC\Router
 */
class Path implements Middleware
{
    protected PathRegExp $path;
    protected RequestHandler $handler;

    /**
     * @param string $path
     * @param Next $handler
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
     * @param Next $handler
     */
    public function __construct(PathRegExp $path, RequestHandler $handler)
    {
        $this->path = $path;
        $this->handler = $handler;
    }

    /**
     * @param Request $request
     * @param Next $next
     *
     * @return Promise
     */
    public function handleRequest(Request $request, Next $next): Promise
    {
        return call(function () use ($request, $next) {
            // We get the routing uri to match
            $uri = Routing::getUriToMatchFrom($request);

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
            Routing::saveUriToMatchTo($request, $uri->withPath($newPath));
            Routing::saveMatchResultTo($request, $result);

            return yield $this->handler->handleRequest($request);
        });
    }

    /**
     * @param Request $request
     * @param Next $next
     * @return Promise
     */
    protected function postMatchingHook(Request $request, Next $next): Promise
    {
        return call(static function () {
            return null;
        });
    }
}