<?php

declare(strict_types=1);

namespace App\Ipf\Definition;

/**
 * An explicitly declared relation (Icms\Ipf\Metadata\Relation), never
 * inferred from a control's itemHandler.
 */
final class RelationDefinition
{
    public function __construct(
        public readonly string $field,
        public readonly string $target,
        public readonly string $cardinality,
        public readonly string $ownership,
        public readonly ?string $foreignKey,
        public readonly ?string $joinTable,
    ) {
    }
}
