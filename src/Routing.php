<?php
declare(strict_types=1);

namespace MNC\Router;

use Amp\Http\Server\MissingAttributeError;
use Amp\Http\Server\Request;
use MNC\PathToRegExpPHP\MatchResult;
use Psr\Http\Message\UriInterface;

/**
 * Class Routing
 * @package MNC\Router
 */
class Routing
{
    private const URI_ATTR = 'router.uri';
    private const ALLOWED_METHODS_ATTR = 'router.allowed_methods';
    private const MATCH_RESULT_ATTR = 'router.match_result';
    private const PARAMS_ATTR = 'router.params';

    /**
     * @param Request $request
     * @param array $methods
     */
    public static function saveAllowedMethodsTo(Request $request, array $methods): void
    {
        try {
            $internalMethods = $request->getAttribute(self::ALLOWED_METHODS_ATTR);
            $internalMethods = array_merge($internalMethods, $methods);
            $request->setAttribute(self::ALLOWED_METHODS_ATTR, $internalMethods);
        } catch (MissingAttributeError $e) {
            $request->setAttribute(self::ALLOWED_METHODS_ATTR, $methods);
        }
    }

    /**
     * @param Request $request
     * @return UriInterface
     */
    public static function getUriToMatchFrom(Request $request): UriInterface
    {
        try {
            return $request->getAttribute(self::URI_ATTR);
        } catch (MissingAttributeError $e) {
            return $request->getUri();
        }
    }

    /**
     * @param Request $request
     * @param UriInterface $uri
     */
    public static function saveUriToMatchTo(Request $request, UriInterface $uri): void
    {
        $request->setAttribute(self::URI_ATTR, $uri);
    }

    /**
     * @param Request $request
     * @param MatchResult $result
     */
    public static function saveMatchResultTo(Request $request, MatchResult $result): void
    {
        // We inject the matched params if any
        foreach ($result->getValues() as $key => $value) {
            self::saveParam($request, $key, $value);
        }

        $request->setAttribute(self::MATCH_RESULT_ATTR, $result);
    }

    /**
     * @param Request $request
     * @param string $name
     * @param string $value
     */
    public static function saveParam(Request $request, string $name, string $value): void
    {
        try {
            $params = $request->getAttribute(self::PARAMS_ATTR);
            $params[$name] = $value;
            $request->setAttribute(self::PARAMS_ATTR, $params);
        } catch (MissingAttributeError $e) {
            $request->setAttribute(self::PARAMS_ATTR, [$name => $value]);
        } finally {
            $request->setAttribute($name, $value);
        }
    }

    /**
     * @param Request $request
     * @param bool $values
     * @return array
     */
    public static function getParams(Request $request, bool $values = false): array
    {
        try {
            $paramNames = $request->getAttribute(self::PARAMS_ATTR);
            $attrs = $request->getAttributes();
            $params = [];
            foreach ($paramNames as $paramName) {
                $params[$paramName] = $attrs[$paramName] ?? null;
            }
            if ($values === true) {
                $params = array_values($params);
            }

            return $params;
        } catch (MissingAttributeError $e) {
            return [];
        }
    }
}