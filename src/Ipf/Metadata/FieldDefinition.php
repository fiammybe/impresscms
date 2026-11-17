<?php

declare(strict_types=1);

namespace App\Ipf\Metadata;

/**
 * One variable of an IPF object, as its definition declares it (design D2).
 */
final class FieldDefinition
{
    /**
     * @param int                       $dataType one of the XOBJ_DTYPE_* values
     * @param array<string, mixed>|null $control  the form control, if the object sets one
     */
    public function __construct(
        public readonly string $name,
        public readonly int $dataType,
        public readonly bool $required = false,
        public readonly ?int $maxLength = null,
        public readonly string $caption = '',
        public readonly string $description = '',
        public readonly ?array $control = null,
        public readonly bool $persistent = true,
        public readonly bool $sortable = true,
        public readonly bool $multilingual = false,
        public readonly bool $displayOnForm = true,
        public readonly bool $readOnly = false,
    ) {
    }
}
