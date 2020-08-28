<?php
declare(strict_types=1);


namespace MNC\Router;

use Amp\Http\Server\MissingAttributeError;
use Amp\Http\Server\Request;
use Psr\Http\Message\UriInterface;

/**
 * Class RoutingContext
 * @package MNC\Router
 */
class RoutingContext
{
    private const ATTR = 'routing.context';

    /**
     * @param Request $request
     * @return RoutingContext
     */
    public static function from(Request $request): RoutingContext
    {
        try {
            return $request->getAttribute(self::ATTR);
        } catch (MissingAttributeError $e) {
            $context = new self($request->getUri());
            $request->setAttribute(self::ATTR, $context);
            return $context;
        }
    }

    public UriInterface $uriToMatch;
    public array $params;

    /**
     * RoutingContext constructor.
     * @param UriInterface $uriToMatch
     */
    protected function __construct(UriInterface $uriToMatch)
    {
        $this->uriToMatch = $uriToMatch;
        $this->params = [];
    }

    /**
     * @param UriInterface $uriToMatch
     */
    public function setUriToMatch(UriInterface $uriToMatch): void
    {
        $this->uriToMatch = $uriToMatch;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function addParam(string $name, string $value): void
    {
        $this->params[$name] = $value;
    }
}