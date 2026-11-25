<?php

declare(strict_types=1);

namespace App\Takeover;

use App\Module\ModuleRegistry;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;

/**
 * The takeovers of legacy script URLs (Takeover), registered by the host and
 * the module providers, and the decision which one, if any, owns a request.
 *
 * routes/web.php adds one ordinary route per taken-over path (paths()), so
 * the route table stays standard and cacheable; match() runs for requests
 * to those paths only, without Laravel's route matcher.
 */
final class TakeoverRegistry
{
    /** @var array<string, Takeover> */
    private array $takeovers = [];

    public function __construct(
        private readonly Container $container,
        private readonly ModuleRegistry $modules,
    ) {
    }

    public function add(Takeover $takeover): void
    {
        $this->takeovers[$takeover->key] = $takeover;
    }

    /**
     * @return array<string, Takeover> in registration order
     */
    public function all(): array
    {
        return $this->takeovers;
    }

    /**
     * The methods taken over on each path, for the routes of routes/web.php.
     *
     * @return array<string, list<string>> path => methods
     */
    public function paths(): array
    {
        $paths = [];
        foreach ($this->takeovers as $takeover) {
            $methods = array_map('strtoupper', $takeover->methods);
            $paths[$takeover->path()] = array_values(array_unique([...($paths[$takeover->path()] ?? []), ...$methods]));
        }

        ksort($paths);

        return $paths;
    }

    /**
     * The takeover that owns the request: the first registered one whose
     * path, method, parameters and conditions match, that is switched on and
     * whose module is active; null when legacy owns it.
     */
    public function match(Request $request): ?Takeover
    {
        foreach ($this->candidates($request) as $takeover) {
            if ($this->isEnabled($takeover)) {
                return $takeover;
            }
        }

        return null;
    }

    /**
     * The takeover that would own the request if every takeover were
     * switched on, for `icms:routes:ownership`.
     */
    public function matchIgnoringSwitches(Request $request): ?Takeover
    {
        return $this->candidates($request)[0] ?? null;
    }

    public function isEnabled(Takeover $takeover): bool
    {
        if (in_array($takeover->key, (array) config('icms.takeovers.disabled', []), true)) {
            return false;
        }

        return $takeover->module === null || $this->modules->isEnabled($takeover->module);
    }

    /**
     * @return list<Takeover>
     */
    private function candidates(Request $request): array
    {
        $path = '/' . trim($request->getPathInfo(), '/');
        $candidates = [];
        foreach ($this->takeovers as $takeover) {
            if ($takeover->path() !== $path || ! $takeover->matchesParameters($request)) {
                continue;
            }

            foreach ($takeover->conditions as $condition) {
                if (! $this->container->make($condition)->matches($request)) {
                    continue 2;
                }
            }

            $candidates[] = $takeover;
        }

        return $candidates;
    }
}
