<?php

declare(strict_types=1);

namespace App\Routing;

use App\LegacyBridge\LegacyBridge;
use App\Takeover\TakeoverRegistry;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Which application owns a request.
 *
 * Laravel routes and legacy ImpressCMS share one URL space: the legacy bridge
 * is an all-method route marked as fallback, so the router itself decides
 * (design L4). Because that route accepts every method, the router never
 * answers 405 on its own; allowedMethods() finds the methods of the other
 * routes that match the path, so the bridge can answer 405 instead of running
 * legacy code. A legacy script route (LegacyScriptController) only counts
 * when one of its takeovers claims the request.
 *
 * Works on the live and on the cached (compiled) route collection.
 */
final class RouteOwnership
{
    public const LEGACY_ROUTE = 'icms.legacy';
    public const RESERVED_ROUTE_PREFIX = 'icms.reserved.';
    public const SCRIPT_ROUTE_PREFIX = 'icms.script:';

    private const PROBE_METHODS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

    public function __construct(
        private readonly Router $router,
        private readonly LegacyBridge $bridge,
        private readonly TakeoverRegistry $takeovers,
    ) {
    }

    /**
     * Methods accepted by Laravel routes (other than the legacy fallback) for
     * the request's path and parameters; empty when no Laravel route matches them.
     *
     * @return list<string>
     */
    public function allowedMethods(Request $request): array
    {
        $allowed = [];
        foreach (self::PROBE_METHODS as $method) {
            $probe = $request->duplicate();
            $probe->setMethod($method);

            $route = $this->match($probe);
            if ($route !== null && $this->ownsRequest($route, $probe)) {
                $allowed[] = $method;
            }
        }

        if (in_array('GET', $allowed, true)) {
            array_splice($allowed, (int) array_search('GET', $allowed, true) + 1, 0, ['HEAD']);
        }

        return $allowed;
    }

    /**
     * The owner of a request, for `icms:routes:ownership`.
     *
     * @return array{
     *     owner: string,
     *     route: ?string,
     *     detail: string
     * }
     */
    public function describe(Request $request): array
    {
        try {
            $route = $this->router->getRoutes()->match($request);
        } catch (HttpExceptionInterface $e) {
            return ['owner' => 'laravel', 'route' => null, 'detail' => "no route matches ({$e->getStatusCode()})"];
        }

        $name = (string) $route->getName();
        if (str_starts_with($name, self::RESERVED_ROUTE_PREFIX)) {
            return ['owner' => 'laravel', 'route' => $name, 'detail' => 'reserved prefix: 404, never legacy'];
        }

        if (str_starts_with($name, self::SCRIPT_ROUTE_PREFIX)) {
            $takeover = $this->takeovers->match($request);
            if ($takeover !== null) {
                return ['owner' => 'laravel', 'route' => $name, 'detail' => "takeover {$takeover->key}"];
            }
        } elseif ($name !== self::LEGACY_ROUTE) {
            return ['owner' => 'laravel', 'route' => $name, 'detail' => 'Laravel route'];
        }

        $allowed = $this->allowedMethods($request);
        if ($allowed !== []) {
            $methods = implode(', ', $allowed);

            return ['owner' => 'laravel', 'route' => null, 'detail' => "405 Method Not Allowed (Allow: {$methods})"];
        }

        try {
            $resolution = $this->bridge->resolve($request);
        } catch (HttpExceptionInterface $e) {
            return ['owner' => 'legacy', 'route' => $name, 'detail' => "{$e->getStatusCode()}: {$e->getMessage()}"];
        }

        $detail = "{$resolution->scriptName} ({$resolution->strategy})";
        $disabled = $this->takeovers->matchIgnoringSwitches($request);
        if ($disabled !== null) {
            $detail = "{$detail}; takeover {$disabled->key} disabled";
        }

        return ['owner' => 'legacy', 'route' => $name, 'detail' => $detail];
    }

    private function match(Request $request): ?Route
    {
        try {
            return $this->router->getRoutes()->match($request);
        } catch (HttpExceptionInterface) {
            return null;
        }
    }

    private function ownsRequest(Route $route, Request $request): bool
    {
        $name = (string) $route->getName();
        if ($name === self::LEGACY_ROUTE) {
            return false;
        }

        return ! str_starts_with($name, self::SCRIPT_ROUTE_PREFIX) || $this->takeovers->match($request) !== null;
    }
}
