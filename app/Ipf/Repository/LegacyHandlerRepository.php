<?php

declare(strict_types=1);

namespace App\Ipf\Repository;

use App\Ipf\Resource\LegacyResource;
use App\Ipf\Resource\ResourceDescriptor;

/**
 * The items of an IPF resource through its legacy handler, as
 * Icms\Ipf\Controller and Icms\Ipf\View\Table use it:
 *
 * - save(): setVar() for each given writable value, then the handler's
 *   insert(), which runs cleanVars() and the events beforeSave,
 *   beforeInsert/beforeUpdate, afterSave, afterInsert/afterUpdate; then
 *   each permission's groups through Icms\Ipf\Permission\Handler, replacing
 *   the stored ones (none when missing)
 * - delete(): the handler's delete(), which runs beforeDelete/afterDelete
 *   and removes the item's permissions
 * - paginate(): getObjects()/getCount() with the quick search as LIKE
 *   conditions combined with OR, as the legacy table builds them
 *
 * Table names, ids, module context, errors and event order are the
 * handler's. Uploads are not handled: a resource with upload controls is not
 * compiled for validation, so it stays on the legacy page.
 */
final class LegacyHandlerRepository implements IpfResourceRepository
{
    /** @var class-string<ResourceDescriptor> */
    private readonly string $descriptor;

    public function __construct(private readonly LegacyResource $resource)
    {
        $this->descriptor = $resource->descriptor;
    }

    public function find(int $id): ?IpfRecord
    {
        $entity = $this->resource->handler()->get($id);

        return $entity->isNew() ? null : $this->record($entity);
    }

    public function make(): IpfRecord
    {
        return $this->record($this->resource->handler()->create());
    }

    public function save(?int $id, array $values, array $permissions): SaveResult
    {
        $handler = $this->resource->handler();
        $entity = $id !== null && $id > 0 ? $handler->get($id) : $handler->create();
        $created = $entity->isNew();

        $writable = $this->descriptor::writableFields();
        foreach ($values as $field => $value) {
            if (in_array($field, $writable, true)) {
                EntityValues::write($entity, $field, $value);
            }
        }

        if (! $handler->insert($entity)) {
            return new SaveResult(null, $created, self::errors($entity, 'The handler did not store the item.'));
        }

        $itemId = (int) $entity->id();
        $store = new \Icms\Ipf\Permission\Handler($handler);
        foreach ($this->descriptor::permissions() as $permission) {
            // as in legacy, a selection the browser did not send removes every group
            $store->saveItem_Permissions($permissions[$permission] ?? [], $itemId, $permission);
        }

        return new SaveResult($itemId, $created);
    }

    public function delete(int $id): SaveResult
    {
        $entity = $this->resource->handler()->get($id);
        if ($entity->isNew()) {
            return new SaveResult($id, false, found: false);
        }

        if (! $entity->handler->delete($entity)) {
            return new SaveResult($id, false, self::errors($entity, 'The handler did not delete the item.'));
        }

        return new SaveResult($id, false);
    }

    public function paginate(ListQuery $query): ListPage
    {
        $handler = $this->resource->handler();
        $criteria = new \Icms\Db\Criteria\Compo();
        $search = $this->descriptor::listView()['quickSearch'] ?? [];
        if ($query->search !== null && $search !== []) {
            $any = new \Icms\Db\Criteria\Compo();
            foreach ($search as $field) {
                $any->add(new \Icms\Db\Criteria\Item($field, "%{$query->search}%", 'LIKE'), 'OR');
            }

            $criteria->add($any);
        }

        $total = (int) $handler->getCount($criteria);
        $criteria->setSort($query->sort);
        $criteria->setOrder($query->order);
        if ($query->limit !== null) {
            $criteria->setLimit($query->limit);
            $criteria->setStart($query->start);
        }

        $records = array_map(fn (object $entity): IpfRecord => $this->record($entity, false), array_values((array) $handler->getObjects($criteria)));

        return new ListPage($records, $total, $query);
    }

    private function record(object $entity, bool $withPermissions = true): IpfRecord
    {
        $values = [];
        foreach (array_keys($this->descriptor::fields()) as $field) {
            $values[$field] = EntityValues::read($entity, $field);
        }

        $permissions = [];
        if ($withPermissions) {
            foreach ($this->descriptor::permissions() as $permission) {
                $permissions[$permission] = $entity->isNew()
                    ? $this->resource->defaultGroups($permission)
                    : array_values(array_map('intval', (array) ($entity->getGroupPerm($permission) ?: [])));
            }
        }

        return new IpfRecord($entity->isNew() ? null : (int) $entity->id(), $values, $permissions);
    }

    /**
     * @return list<string>
     */
    private static function errors(object $entity, string $fallback): array
    {
        $errors = array_values(array_map('strval', (array) $entity->getErrors()));

        return $errors !== [] ? $errors : [$fallback];
    }
}
