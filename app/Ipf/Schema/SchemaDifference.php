<?php

declare(strict_types=1);

namespace App\Ipf\Schema;

/**
 * One difference between a resource's schema contract and its live table.
 *
 * - compatible: legacy and Laravel both work with the table as it is
 *   (reported for review, never changed)
 * - additive: something the definition needs is missing and can be added
 *   without touching what legacy uses (a column, a join table)
 * - incompatible: needs a human decision; never changed automatically
 */
final class SchemaDifference
{
    public const COMPATIBLE = 'compatible';
    public const ADDITIVE = 'additive';
    public const INCOMPATIBLE = 'incompatible';

    /**
     * @param array<string, mixed> $column the contract's column, for additive differences
     */
    public function __construct(
        public readonly string $kind,
        public readonly string $subject,
        public readonly string $detail,
        public readonly array $column = [],
    ) {
    }
}
