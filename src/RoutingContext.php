<?php
declare(strict_types=1);


namespace MNC\Router;

use Amp\Http\Server\MissingAttributeError;
use Amp\Http\Server\Request;
use MNC\PathToRegExpPHP\MatchResult;
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
    public static function of(Request $request): RoutingContext
    {
        try {
            return $request->getAttribute(self::ATTR);
        } catch (MissingAttributeError $e) {
            $context = new self($request->getUri());
            $request->setAttribute(self::ATTR, $context);
            return $context;
        }
    }

    private UriInterface $uriToMatch;
    private ?MatchResult $lastMatchResult;
    private array $params;
    private array $allowedMethods;

    /**
     * RoutingContext constructor.
     * @param UriInterface $uriToMatch
     */
    protected function __construct(UriInterface $uriToMatch)
    {
        $this->uriToMatch = $uriToMatch;
        $this->lastMatchResult = null;
        $this->params = [];
        $this->allowedMethods = [];
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

    /**
     * @return bool
     */
    public function isMethodNotAllowed(): bool
    {
        return count($this->allowedMethods) > 0;
    }

    /**
     * @return UriInterface
     */
    public function getUriToMatch(): UriInterface
    {
        return $this->uriToMatch;
    }

    /**
     * @return MatchResult|null
     */
    public function getLastMatchResult(): ?MatchResult
    {
        return $this->lastMatchResult;
    }

    /**
     * @param MatchResult $result
     */
    public function setLastMatchResult(MatchResult $result): void
    {
        $this->lastMatchResult = $result;
        foreach ($result->getValues() as $key => $value) {
            $this->addParam($key, $value);
        }
    }

    /**
     * @param string ...$methods
     */
    public function addAllowedMethods(string ...$methods): void
    {
        $this->allowedMethods = array_merge($this->allowedMethods, $methods);
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function getParam(string $key): ?string
    {
        return $this->params[$key] ?? null;
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function saveParam(string $key, string $value): void
    {
        $this->params[$key] = $value;
    }
}