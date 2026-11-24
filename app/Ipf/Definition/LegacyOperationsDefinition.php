<?php

declare(strict_types=1);

namespace App\Ipf\Definition;

/**
 * The legacy admin URL of a resource and its `op` values
 * (Icms\Ipf\Metadata\LegacyOperations).
 */
final class LegacyOperationsDefinition
{
    /**
     * @param array<string, string> $match
     */
    public function __construct(
        public readonly string $script,
        public readonly array $match,
        public readonly string $form,
        public readonly string $save,
        public readonly string $delete,
        public readonly string $confirm,
    ) {
    }
}
