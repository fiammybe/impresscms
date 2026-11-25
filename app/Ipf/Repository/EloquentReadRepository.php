<?php

declare(strict_types=1);

namespace App\Ipf\Repository;

use App\Ipf\Eloquent\IpfModel;
use App\Ipf\Resource\ResourceDescriptor;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Builder;

/**
 * Reads an IPF resource with its generated Eloquent model and its permissions
 * from group_permission; creates, stores and deletes through the legacy
 * repository, so the handler's cleanVars(), events and permission storage
 * still run. Legacy and Laravel read and write the same rows.
 */
final class EloquentReadRepository implements IpfResourceRepository
{
    /**
     * @param class-string<IpfModel> $model
     * @param class-string<ResourceDescriptor> $descriptor
     */
    public function __construct(
        private readonly IpfResourceRepository $writes,
        private readonly string $model,
        private readonly string $descriptor,
        private readonly ConnectionInterface $connection,
    ) {
    }

    public function find(int $id): ?IpfRecord
    {
        $model = $this->model::query()->find($id);

        return $model === null ? null : $this->record($model, $this->permissions($id));
    }

    public function make(): IpfRecord
    {
        return $this->writes->make();
    }

    public function save(?int $id, array $values, array $permissions): SaveResult
    {
        return $this->writes->save($id, $values, $permissions);
    }

    public function delete(int $id): SaveResult
    {
        return $this->writes->delete($id);
    }

    public function paginate(ListQuery $query): ListPage
    {
        $builder = $this->model::query();
        $search = $this->descriptor::listView()['quickSearch'] ?? [];
        if ($query->search !== null && $search !== []) {
            $builder->where(static function (Builder $any) use ($search, $query): void {
                foreach ($search as $field) {
                    $any->orWhere($field, 'like', "%{$query->search}%");
                }
            });
        }

        $total = (clone $builder)->count();
        $builder->orderBy($query->sort, $query->order);
        if ($query->limit !== null) {
            $builder->limit($query->limit)->offset($query->start);
        }

        $records = $builder->get()->map(fn (IpfModel $model): IpfRecord => $this->record($model, []))->values()->all();

        return new ListPage($records, $total, $query);
    }

    /**
     * @param array<string, list<int>> $permissions
     */
    private function record(IpfModel $model, array $permissions): IpfRecord
    {
        $values = [];
        foreach ($this->descriptor::fields() as $field => $definition) {
            $value = $definition['persistent'] ? $model->getAttribute($field) : null;
            $values[$field] = match (true) {
                $value === null, is_array($value), is_int($value) => $value,
                default => (string) $value,
            };
        }

        return new IpfRecord((int) $model->getKey(), $values, $permissions);
    }

    /**
     * The groups of each permission of an item: group_permission rows of the
     * resource's module (Icms\Ipf\Permission\Handler::getGrantedGroups()).
     *
     * @return array<string, list<int>>
     */
    private function permissions(int $id): array
    {
        $permissions = $this->descriptor::permissions();
        if ($permissions === []) {
            return [];
        }

        $module = (int) $this->connection->table('modules')->where('dirname', $this->descriptor::data()['module'])->value('mid');
        $rows = $this->connection->table('group_permission')
            ->where('gperm_modid', $module)
            ->where('gperm_itemid', $id)
            ->whereIn('gperm_name', $permissions)
            ->orderBy('gperm_groupid')
            ->get(['gperm_name', 'gperm_groupid']);

        $groups = array_fill_keys($permissions, []);
        foreach ($rows as $row) {
            $groups[$row->gperm_name][] = (int) $row->gperm_groupid;
        }

        return $groups;
    }
}
