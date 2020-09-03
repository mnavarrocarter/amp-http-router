<?php
declare(strict_types=1);

namespace MNC\Router;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Promise;
use MNC\PathToRegExpPHP\PathRegExp;
use MNC\PathToRegExpPHP\PathRegExpFactory;
use function Amp\call;

/**
 * Class Route
 * @package MNC\Router
 */
class Route extends Path
{
    /**
     * @var string[]
     */
    private array $methods;

    /**
     * @param array $methods
     * @param string $path
     * @param RequestHandler $handler
     * @return Route
     */
    public static function fromPath(array $methods, string $path, RequestHandler $handler): self
    {
        return new self($methods, PathRegExpFactory::create($path), $handler);
    }

    /**
     * Route constructor.
     *
     * @param array $methods
     * @param PathRegExp $path
     * @param RequestHandler $handler
     */
    public function __construct(array $methods, PathRegExp $path, RequestHandler $handler)
    {
        $this->methods = $methods;
        parent::__construct($path, $handler);
    }

    /**
     * @param Request $request
     * @param RequestHandler $next
     * @return Promise
     */
    protected function postMatchingHook(Request $request, RequestHandler $next): Promise
    {
        return call(function () use ($request, $next) {
            // If method does not match but the path does, then we save a method not allowed attr in the request
            if (!$this->methodMatches($request->getMethod())) {
                RoutingContext::of($request)->addAllowedMethods(...$this->methods);
                return yield $next->handleRequest($request);
            }

            return null;
        });
    }

    /**
     * @param string $method
     *
     * @return bool
     */
    protected function methodMatches(string $method): bool
    {
        return \in_array($method, $this->methods, true);
    }
}