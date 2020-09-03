<?php
declare(strict_types=1);

namespace MNC\Router;

use Amp\Http\Server\HttpServer;
use Amp\Http\Server\Middleware;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\ServerObserver;
use Amp\Promise;

/**
 * Class Router
 * @package MNC\Router
 */
class Router implements RequestHandler, ServerObserver
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
     * @param RequestHandler|null $handler
     * @return static
     */
    public static function create(RequestHandler $handler = null): self
    {
        return new self($handler);
    }

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
        RoutingContext::of($request);
        $handler = Middleware\stack($this->handler, ...$this->middleware);
        return $handler->handleRequest($request);
    }

    /** @inheritdoc */
    public function onStart(HttpServer $server): Promise
    {
        $promises = [];
        foreach ($this->middleware as $middleware) {
            if ($middleware instanceof ServerObserver) {
                $promises[] = $middleware->onStart($server);
            }
        }

        return Promise\all($promises);
    }

    /** @inheritdoc */
    public function onStop(HttpServer $server): Promise
    {
        $promises = [];
        foreach ($this->middleware as $middleware) {
            if ($middleware instanceof ServerObserver) {
                $promises[] = $middleware->onStop($server);
            }
        }

        return Promise\all($promises);
    }
}