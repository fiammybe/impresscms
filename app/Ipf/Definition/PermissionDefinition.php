<?php

declare(strict_types=1);

namespace App\Ipf\Definition;

/**
 * A permission of an IPF handler (Icms\Ipf\Handler::addPermission()): per
 * item, stored by group in group_permission under the module's id.
 */
final class PermissionDefinition
{
    public function __construct(
        public readonly string $name,
        public readonly string $caption,
        public readonly string $description,
    ) {
    }
}
