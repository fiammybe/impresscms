<?php

declare(strict_types=1);

namespace App\Ipf\Definition;

/**
 * The form control of a variable: the one it set with setControl(), or the
 * one Icms\Ipf\Form\Base gives its data type; `hidden` for the key and the
 * variables not shown on the form.
 */
final class ControlDefinition
{
    /** controls that post a list (`name[]`) */
    private const MULTIPLE = ['selectmulti', 'group_multi', 'user_multi', 'theme_multi'];

    /**
     * @param array<string, scalar|null> $settings presentational settings (size, rows, cols, …)
     */
    public function __construct(
        public readonly string $name,
        public readonly bool $explicit,
        public readonly ?OptionProvider $options = null,
        public readonly array $settings = [],
    ) {
    }

    public function isMultiple(): bool
    {
        return in_array($this->name, self::MULTIPLE, true);
    }
}
