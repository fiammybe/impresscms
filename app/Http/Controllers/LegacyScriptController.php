<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Routing\RouteOwnership;
use App\Takeover\MiddlewareDispatcher;
use App\Takeover\TakeoverRegistry;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The route of a legacy script URL that some takeovers claim, for example
 * `/modules/system/admin.php`: an ordinary, cacheable route (routes/web.php).
 *
 * Laravel's route matcher cannot select a route by query string or POST body,
 * so the takeover registry decides here: the matching takeover's action runs
 * behind its middleware, and any other request to the URL goes to the legacy
 * bridge, exactly as if no route claimed it.
 */
final class LegacyScriptController
{
    public const TAKEOVER_ATTRIBUTE = 'icms_takeover';

    public function __construct(
        private readonly TakeoverRegistry $takeovers,
        private readonly MiddlewareDispatcher $dispatcher,
        private readonly LegacyBridgeController $bridge,
        private readonly Container $container,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $takeover = $this->takeovers->match($request);
        if ($takeover === null) {
            $request->attributes->set(self::TAKEOVER_ATTRIBUTE, RouteOwnership::LEGACY_ROUTE);

            return ($this->bridge)($request);
        }

        $request->attributes->set(self::TAKEOVER_ATTRIBUTE, $takeover->key);

        return $this->dispatcher->dispatch(
            $request,
            $takeover->middleware,
            // Class@method: the container builds the controller, then calls the method
            fn (): mixed => $this->container->call(is_array($takeover->action) ? implode('@', $takeover->action) : $takeover->action, $takeover->parameters),
        );
    }
}
