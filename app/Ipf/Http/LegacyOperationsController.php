<?php

declare(strict_types=1);

namespace App\Ipf\Http;

use App\Ipf\Resource\ResourceDescriptor;
use App\Ipf\Resource\ResourceRegistry;
use App\Takeover\MiddlewareDispatcher;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Translates the legacy admin URL of an IPF resource,
 * `admin.php?fct=<item>&op=<op>`, into the actions of its resource
 * controller, with the `op` values the handler declares
 * (Icms\Ipf\Metadata\LegacyOperations). `op` and the key are read from the
 * POST body, else the query string, as the legacy script extracts them.
 *
 * | op                   | key   | action           |
 * |----------------------|-------|------------------|
 * | (none, other)        |       | index            |
 * | form (`mod`)         | no    | create           |
 * | form                 | yes   | edit             |
 * | save (`add…`)        | no    | store            |
 * | save                 | yes   | update           |
 * | delete (`del`)       |       | confirmDestroy   |
 * | delete + `confirm`   |       | destroy          |
 *
 * Saving and confirmed deletions need a security token (icms.token:legacy).
 * The controller does not see the query-string conventions.
 */
final class LegacyOperationsController
{
    public function __construct(
        private readonly ResourceRegistry $resources,
        private readonly MiddlewareDispatcher $dispatcher,
        private readonly Container $container,
    ) {
    }

    public function handle(Request $request, string $resource): Response
    {
        $registration = $this->resources->get($resource);
        /** @var class-string<ResourceDescriptor> $descriptor */
        $descriptor = $registration->generatedClass('Descriptor');
        $legacy = (array) $descriptor::legacy();
        $key = $descriptor::primaryKey();

        $op = self::input($request, 'op');
        $id = (int) self::input($request, $key);

        [$action, $parameters, $middleware] = match ($op) {
            $legacy['form'] => $id > 0 ? ['edit', ['id' => $id], []] : ['create', [], []],
            // the legacy save loads the item by the posted key only
            $legacy['save'] => (int) $request->request->get($key, 0) > 0
                ? ['update', ['id' => (int) $request->request->get($key)], ['icms.token:legacy']]
                : ['store', [], ['icms.token:legacy']],
            $legacy['delete'] => $request->request->get($legacy['confirm'])
                ? ['destroy', ['id' => $id], ['icms.token:legacy']]
                : ['confirmDestroy', ['id' => $id], []],
            default => ['index', [], []],
        };

        return $this->dispatcher->dispatch(
            $request,
            $middleware,
            fn (): mixed => $this->container->call([$this->container->make((string) $registration->controller), $action], $parameters),
        );
    }

    private static function input(Request $request, string $name): string
    {
        // all(): get() refuses arrays, which are ignored here as legacy (int) casts them
        $body = $request->request->all();
        $value = array_key_exists($name, $body) ? $body[$name] : ($request->query->all()[$name] ?? '');

        return is_scalar($value) ? (string) $value : '';
    }
}
