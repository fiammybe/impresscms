<?php

declare(strict_types=1);

namespace App\Takeover;

use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Routing\Pipeline;
use Illuminate\Routing\Router;
use Symfony\Component\HttpFoundation\Response;

/**
 * Runs an action behind Laravel middleware, as the router runs a route's:
 * aliases and groups resolved by the router, and exceptions rendered into
 * responses inside the pipeline (Illuminate\Routing\Pipeline), so a
 * middleware that sets up legacy state (icms.admin) is still around while
 * the response is rendered.
 */
final class MiddlewareDispatcher
{
    public function __construct(
        private readonly Router $router,
        private readonly Container $container,
    ) {
    }

    /**
     * @param list<string> $middleware
     * @param Closure(Request): mixed $action
     */
    public function dispatch(Request $request, array $middleware, Closure $action): Response
    {
        return (new Pipeline($this->container))
            ->send($request)
            ->through($this->router->resolveMiddleware($middleware))
            ->then(fn (Request $request): Response => $this->router->prepareResponse($request, $action($request)));
    }
}
