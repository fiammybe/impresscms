<?php

declare(strict_types=1);

namespace App\Ipf\Definition;

/**
 * Where the choices of a select control come from, in the order
 * Icms\Ipf\Form\Elements\Select looks for them:
 *
 * - `literal`: the control's `options` array
 * - `object`: a method of an object the entity put in the control
 *   (`'object' => &$this`); only known at runtime
 * - `handler`: a method of the entity's own handler
 * - `itemHandler`: a method of another handler, of a module (`module`) or
 *   of the core (icms::handler())
 *
 * A provider only offers choices. It is never a relation (RelationDefinition).
 */
final class OptionProvider
{
    public const LITERAL = 'literal';
    public const OBJECT = 'object';
    public const HANDLER = 'handler';
    public const ITEM_HANDLER = 'itemHandler';

    /**
     * @param list<mixed> $params arguments of the method
     * @param array<int|string, string> $options the choices of a literal provider
     */
    public function __construct(
        public readonly string $kind,
        public readonly ?string $handler = null,
        public readonly ?string $module = null,
        public readonly ?string $method = null,
        public readonly array $params = [],
        public readonly array $options = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'kind' => $this->kind,
            'handler' => $this->handler,
            'module' => $this->module,
            'method' => $this->method,
            'params' => $this->params,
            'options' => $this->options,
        ], static fn (mixed $value): bool => $value !== null && $value !== []);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            (string) $data['kind'],
            $data['handler'] ?? null,
            $data['module'] ?? null,
            $data['method'] ?? null,
            $data['params'] ?? [],
            $data['options'] ?? [],
        );
    }
}
