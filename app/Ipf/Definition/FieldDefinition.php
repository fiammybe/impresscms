<?php

declare(strict_types=1);

namespace App\Ipf\Definition;

/**
 * One variable of an IPF entity (Icms\Ipf\Entity::$vars and $controls).
 *
 * Captions and descriptions are language strings the entity resolves when it
 * is built. They belong to the definition read at runtime, not to the
 * generated artifacts, which stay language independent.
 */
final class FieldDefinition
{
    public function __construct(
        public readonly string $name,
        public readonly DataType $type,
        public readonly mixed $default,
        public readonly bool $required,
        public readonly ?int $maxLength,
        public readonly bool $persistent,
        public readonly bool $displayOnForm,
        public readonly bool $displayOnSingleView,
        public readonly bool $readonly,
        public readonly bool $sortable,
        public readonly bool $multilingual,
        public readonly ?string $valueOptions,
        public readonly ?ControlDefinition $control,
        public readonly string $caption,
        public readonly string $description,
        public readonly bool $isKey,
    ) {
    }

    public function isHidden(): bool
    {
        return $this->control?->name === 'hidden';
    }

    /**
     * Whether the legacy form has an element for it, a hidden one included.
     */
    public function onForm(): bool
    {
        return $this->control !== null;
    }
}
