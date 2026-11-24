<?php

declare(strict_types=1);

namespace App\Takeover;

use Illuminate\Http\Request;

/**
 * Laravel's ownership of some variants of a legacy script URL, for example
 * `/modules/system/admin.php` with `fct=mimetype`.
 *
 * Laravel's route matcher, cached or not, matches paths and methods only, so
 * the URL itself is an ordinary route to LegacyScriptController, and this
 * entry decides per request whether Laravel or the legacy script answers it:
 *
 * - `query`: parameters the query string must have
 * - `input`: parameters the POST body must have or, when the body lacks
 *   them, the query string (admin.php extracts $_POST after $_GET)
 * - `conditions`: TakeoverCondition classes, resolved from the container
 *
 * A switched-off takeover (ICMS_TAKEOVERS_DISABLED) or one of an inactive
 * module never matches, so the legacy script runs.
 */
final class Takeover
{
    /**
     * @param list<string> $methods
     * @param array<string, string> $query
     * @param array<string, string> $input
     * @param list<class-string<TakeoverCondition>> $conditions
     * @param array{0: class-string, 1: string}|class-string $action a controller method or an invokable controller
     * @param array<string, mixed> $parameters named arguments of the action
     * @param list<string> $middleware Laravel middleware run before the action
     * @param string|null $module the ImpressCMS module that must be active
     */
    public function __construct(
        public readonly string $key,
        public readonly string $path,
        public readonly array $methods,
        public readonly array|string $action,
        public readonly array $query = [],
        public readonly array $input = [],
        public readonly array $conditions = [],
        public readonly array $parameters = [],
        public readonly array $middleware = [],
        public readonly ?string $module = null,
    ) {
    }

    public function path(): string
    {
        return '/' . trim($this->path, '/');
    }

    /**
     * Whether the request's method and parameters select this takeover;
     * the switch, the module state and the conditions are checked by the
     * registry.
     */
    public function matchesParameters(Request $request): bool
    {
        if (! in_array($request->getMethod(), $this->allowedMethods(), true)) {
            return false;
        }

        foreach ($this->query as $name => $value) {
            if ((string) $request->query->get($name) !== $value) {
                return false;
            }
        }

        foreach ($this->input as $name => $value) {
            $actual = $request->request->has($name) ? $request->request->get($name) : $request->query->get($name);
            if (! is_scalar($actual) || (string) $actual !== $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return list<string>
     */
    public function allowedMethods(): array
    {
        $methods = array_map('strtoupper', $this->methods);

        return in_array('GET', $methods, true) && ! in_array('HEAD', $methods, true) ? [...$methods, 'HEAD'] : $methods;
    }
}
