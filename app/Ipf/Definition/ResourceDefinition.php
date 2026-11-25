<?php

declare(strict_types=1);

namespace App\Ipf\Definition;

use OutOfBoundsException;

/**
 * The canonical, framework-neutral definition of an IPF resource: an entity,
 * its handler and the explicit metadata on the handler class. Read from the
 * live legacy declaration by DefinitionReader; the Laravel artifacts are
 * generated from it (App\Ipf\Compiler).
 */
final class ResourceDefinition
{
    /**
     * @param array<string, FieldDefinition> $fields in declaration order
     * @param list<PermissionDefinition> $permissions
     * @param list<RelationDefinition> $relations
     * @param list<NotificationDefinition> $notifications
     */
    public function __construct(
        public readonly string $module,
        public readonly string $item,
        public readonly string $table,
        public readonly string $primaryKey,
        public readonly string $identifier,
        public readonly string $summary,
        public readonly array $fields,
        public readonly array $permissions,
        public readonly ?ListDefinition $list,
        public readonly array $relations,
        public readonly ?LegacyOperationsDefinition $legacy,
        public readonly array $notifications,
        public readonly Provenance $provenance,
    ) {
    }

    public function key(): string
    {
        return "{$this->module}.{$this->item}";
    }

    public function field(string $name): FieldDefinition
    {
        return $this->fields[$name] ?? throw new OutOfBoundsException("{$this->key()} has no field \"{$name}\".");
    }

    public function permission(string $name): ?PermissionDefinition
    {
        foreach ($this->permissions as $permission) {
            if ($permission->name === $name) {
                return $permission;
            }
        }

        return null;
    }
}
