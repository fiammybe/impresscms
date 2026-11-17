<?php

declare(strict_types=1);

namespace App\Ipf\Store;

use App\Ipf\Form\IpfEntityDataMapper;
use App\Ipf\Metadata\EntityDefinition;
use App\Ipf\Query\ListQuery;
use App\Legacy\ModuleHandlerLocator;
use Icms\Db\Criteria\Compo;
use Icms\Db\Criteria\Item;

/**
 * IPF objects, through their own handler (design D4): the list options become
 * criteria and the handler runs them, so whatever the handler adds (events,
 * its general SQL) applies, as in Icms\Ipf\View\Table.
 */
final class HandlerStore implements ObjectStoreInterface
{
    public function __construct(private readonly ModuleHandlerLocator $handlers)
    {
    }

    public function supports(EntityDefinition $definition): bool
    {
        return $definition->source === EntityDefinition::SOURCE_IPF;
    }

    public function find(EntityDefinition $definition, int|string $id): ?object
    {
        $object = $this->handler($definition)->get($id);
        if (!is_object($object) || $object->isNew()) {
            return null;
        }

        return $object;
    }

    public function list(EntityDefinition $definition, ListQuery $query): ListResult
    {
        $handler = $this->handler($definition);
        $criteria = new Compo();
        foreach ($query->filters as $field => $value) {
            $criteria->add(new Item($field, $value));
        }

        if ($query->hasSearch()) {
            $search = new Compo();
            foreach ($query->searchFields as $field) {
                $search->add(new Item($field, "%{$query->search}%", 'LIKE'), 'OR');
            }

            $criteria->add($search);
        }

        $total = (int) $handler->getCount($criteria);

        if ($query->sort !== null) {
            $criteria->setSort("{$definition->item}.{$query->sort}");
            $criteria->setOrder($query->order);
        }

        $criteria->setStart($query->offset);
        $criteria->setLimit($query->limit);

        return new ListResult(array_values($handler->getObjects($criteria)), $total);
    }

    public function value(EntityDefinition $definition, object $object, string $field): mixed
    {
        $value = IpfEntityDataMapper::read($object, $field);
        $type = $definition->field($field)->dataType;
        if (in_array($type, [XOBJ_DTYPE_FLOAT, XOBJ_DTYPE_CURRENCY], true)) {
            return (float) $value;
        }

        if (in_array($type, [XOBJ_DTYPE_STIME, XOBJ_DTYPE_MTIME, XOBJ_DTYPE_LTIME, XOBJ_DTYPE_TIME_ONLY], true)) {
            return (int) $value;
        }

        if (is_string($value)) {
            return StoredText::withoutFilterMarkers($value);
        }

        return $value;
    }

    public function display(EntityDefinition $definition, object $object, string $field): string
    {
        $value = $object->getVar($field);
        if (is_array($value)) {
            return implode(', ', array_map('strval', $value));
        }

        return (string) $value;
    }

    public function url(EntityDefinition $definition, object $object): ?string
    {
        $url = (string) $object->getItemLink(true);

        return $url !== '' ? $url : null;
    }

    private function handler(EntityDefinition $definition): object
    {
        return $this->handlers->get($definition->module, $definition->item);
    }
}
