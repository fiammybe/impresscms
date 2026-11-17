<?php

declare(strict_types=1);

namespace App\Ipf\Table;

/**
 * One column of a list, with the options of Icms\Ipf\View\Column.
 */
final class ColumnConfig
{
    /**
     * @param string|null     $align        null for the language's default alignment
     * @param int|string|null $width        null for no fixed width
     * @param string|null     $customMethod a method of the object that returns the cell's HTML
     * @param string|null     $caption      null for the field's caption
     */
    public function __construct(
        public readonly string $field,
        public readonly ?string $align = null,
        public readonly int|string|null $width = null,
        public readonly ?string $customMethod = null,
        public readonly mixed $param = null,
        public readonly ?string $caption = null,
        public readonly bool $sortable = true,
    ) {
    }
}
