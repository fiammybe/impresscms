<?php

declare(strict_types=1);

namespace App\Ipf\Repository;

/**
 * An item of an IPF resource as data: the stored (raw) values by field name,
 * lists for XOBJ_DTYPE_SIMPLE_ARRAY, and the groups of each permission.
 */
final class IpfRecord
{
    /**
     * @param array<string, mixed> $values
     * @param array<string, list<int>> $permissions
     */
    public function __construct(
        public readonly ?int $id,
        public readonly array $values,
        public readonly array $permissions,
    ) {
    }

    public function isNew(): bool
    {
        return $this->id === null;
    }

    public function value(string $field): mixed
    {
        return $this->values[$field] ?? null;
    }

    /**
     * @return list<int>
     */
    public function groups(string $permission): array
    {
        return $this->permissions[$permission] ?? [];
    }
}
