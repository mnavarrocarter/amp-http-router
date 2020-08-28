<?php
declare(strict_types=1);

namespace MNC\Router;

use Amp\Http\Server\Middleware;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Promise;
use function Amp\call;

/**
 * Class Router
 * @package MNC\Router
 */
class Router implements RequestHandler
{
    /**
     * @var RequestHandler
     */
    private RequestHandler $handler;
    /**
     * @var array|Middleware[]
     */
    private array $middleware;

    /**
     * Router constructor.
     * @param RequestHandler|null $handler
     */
    public function __construct(RequestHandler $handler = null)
    {
        $this->handler = $handler ?? new DefaultHandler();
    }

    /**
     * @param Middleware $middleware
     * @return Router
     */
    public function use(Middleware $middleware): Router
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    /**
     * @param string $path
     * @param RequestHandler $handler
     * @return Router
     */
    public function get(string $path, RequestHandler $handler): Router
    {
        $this->use(Route::fromPath(['GET'], $path, $handler));
        return $this;
    }

    /**
     * @param string $path
     * @param RequestHandler $handler
     * @return Router
     */
    public function post(string $path, RequestHandler $handler): Router
    {
        $this->use(Route::fromPath(['POST'], $path, $handler));
        return $this;
    }

    /**
     * @param string $path
     * @param RequestHandler $handler
     * @return Router
     */
    public function patch(string $path, RequestHandler $handler): Router
    {
        $this->use(Route::fromPath(['PATCH'], $path, $handler));
        return $this;
    }

    /**
     * @param string $path
     * @param RequestHandler $handler
     * @return Router
     */
    public function put(string $path, RequestHandler $handler): Router
    {
        $this->use(Route::fromPath(['PUT'], $path, $handler));
        return $this;
    }

    /**
     * @param string $path
     * @param RequestHandler $handler
     * @return Router
     */
    public function delete(string $path, RequestHandler $handler): Router
    {
        $this->use(Route::fromPath(['DELETE'], $path, $handler));
        return $this;
    }

    /**
     * @param string $path
     * @param RequestHandler $handler
     * @return Router
     */
    public function options(string $path, RequestHandler $handler): Router
    {
        $this->use(Route::fromPath(['OPTIONS'], $path, $handler));
        return $this;
    }

    /**
     * @param array $methods
     * @param string $path
     * @param RequestHandler $handler
     * @return Router
     */
    public function route(array $methods, string $path, RequestHandler $handler): Router
    {
        $this->use(Route::fromPath($methods, $path, $handler));
        return $this;
    }

    /**
     * @param string $path
     * @param Router $router
     * @return Router
     */
    public function mount(string $path, Router $router): Router
    {
        $this->use(Path::fromString($path, $router));
        return $this;
    }

    /**
     * @param Request $request
     * @return Promise<Response>
     */
    public function handleRequest(Request $request): Promise
    {
        return call(function () use ($request) {
            RoutingContext::from($request);
            $handler = Middleware\stack($this->handler, ...$this->middleware);
            return yield $handler->handleRequest($request);
        });
    }
}