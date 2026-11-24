<?php

declare(strict_types=1);

namespace App\Ipf\Resource;

use Illuminate\Support\Str;

/**
 * An IPF resource a module compiles into Laravel artifacts: its handler, where
 * its generated code lives and the switches that choose, per concern, between
 * the Laravel implementation and the legacy one (rollback).
 *
 * Declared in the module's `resources.php` (App\Module\ModuleServiceProvider),
 * switched in the module's configuration.
 */
final class ResourceRegistration
{
    public const RENDERER_BLADE = 'blade';
    public const RENDERER_LEGACY = 'legacy';
    public const PERSISTENCE_LEGACY = 'legacy';
    public const PERSISTENCE_ELOQUENT_READ = 'eloquent-read';
    public const VALIDATION_FORM_REQUEST = 'form-request';
    public const VALIDATION_LEGACY = 'legacy';
    public const POLICY_GENERATED = 'generated';
    public const POLICY_LEGACY = 'legacy';

    /**
     * @param string $handler the handler id of App\Legacy\HandlerLocator, `module:<dirname>/<item>`
     * @param string $baseNamespace the module's namespace, for example Modules\System
     * @param string $basePath the module's directory in the host
     * @param array<string, string> $switches renderer, persistence, validation, policy
     * @param class-string|null $controller the resource controller serving its legacy admin URL
     * @param list<string> $middleware middleware of that controller, for example icms.admin:mimetype
     */
    public function __construct(
        public readonly string $module,
        public readonly string $item,
        public readonly string $handler,
        public readonly string $baseNamespace,
        public readonly string $basePath,
        public readonly array $switches = [],
        public readonly ?string $controller = null,
        public readonly array $middleware = [],
    ) {
    }

    public function key(): string
    {
        return "{$this->module}.{$this->item}";
    }

    public function className(): string
    {
        return Str::studly($this->item);
    }

    public function namespace(): string
    {
        return "{$this->baseNamespace}\\Generated\\{$this->className()}";
    }

    public function generatedPath(): string
    {
        return "{$this->basePath}/Generated/{$this->className()}";
    }

    /**
     * @return class-string
     */
    public function generatedClass(string $suffix = ''): string
    {
        return "{$this->namespace()}\\{$this->className()}{$suffix}";
    }

    public function renderer(): string
    {
        return $this->switch('renderer', self::RENDERER_BLADE, [self::RENDERER_BLADE, self::RENDERER_LEGACY]);
    }

    public function persistence(): string
    {
        return $this->switch('persistence', self::PERSISTENCE_LEGACY, [self::PERSISTENCE_LEGACY, self::PERSISTENCE_ELOQUENT_READ]);
    }

    public function validation(): string
    {
        return $this->switch('validation', self::VALIDATION_FORM_REQUEST, [self::VALIDATION_FORM_REQUEST, self::VALIDATION_LEGACY]);
    }

    public function policy(): string
    {
        return $this->switch('policy', self::POLICY_GENERATED, [self::POLICY_GENERATED, self::POLICY_LEGACY]);
    }

    /**
     * @param array<string, string> $switches
     */
    public function withSwitches(array $switches): self
    {
        return new self($this->module, $this->item, $this->handler, $this->baseNamespace, $this->basePath, array_filter($switches) + $this->switches, $this->controller, $this->middleware);
    }

    /**
     * @param list<string> $allowed
     */
    private function switch(string $name, string $default, array $allowed): string
    {
        $value = (string) (($this->switches[$name] ?? '') ?: $default);
        if (! in_array($value, $allowed, true)) {
            throw new \InvalidArgumentException("{$this->key()}: {$name} must be one of " . implode(', ', $allowed) . ", not \"{$value}\".");
        }

        return $value;
    }
}
