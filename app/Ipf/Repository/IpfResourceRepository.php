<?php

declare(strict_types=1);

namespace App\Ipf\Repository;

/**
 * Reading and storing the items of an IPF resource, without knowing how:
 * through the legacy IPF handler (LegacyHandlerRepository) or with reads
 * through the generated Eloquent model (EloquentReadRepository).
 *
 * Values use the legacy field names. save() writes only the given values, as
 * Icms\Ipf\Controller::postDataToObject() writes only the posted keys, and
 * replaces the groups of every handler permission (a permission missing from
 * $permissions has no groups afterwards, as in legacy).
 */
interface IpfResourceRepository
{
    public function find(int $id): ?IpfRecord;

    /**
     * A new item with the definition's defaults and the module's default permissions.
     */
    public function make(): IpfRecord;

    /**
     * @param array<string, mixed> $values validated values by field name
     * @param array<string, list<int>> $permissions groups by permission name
     */
    public function save(?int $id, array $values, array $permissions): SaveResult;

    public function delete(int $id): SaveResult;

    public function paginate(ListQuery $query): ListPage;
}
