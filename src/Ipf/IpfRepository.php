<?php

declare(strict_types=1);

namespace App\Ipf;

use App\Ipf\Metadata\EntityDefinition;
use App\Ipf\Query\ListQuery;
use App\Ipf\Store\ListResult;
use App\Ipf\Store\ObjectStoreInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

/**
 * Reads objects by their description, from the store they belong to
 * (design D4).
 */
final class IpfRepository
{
    /**
     * @param iterable<ObjectStoreInterface> $stores
     */
    public function __construct(
        #[AutowireIterator('icms.ipf.object_store')]
        private readonly iterable $stores,
    ) {
    }

    public function find(EntityDefinition $definition, int|string $id): ?object
    {
        return $this->store($definition)->find($definition, $id);
    }

    public function list(EntityDefinition $definition, ListQuery $query): ListResult
    {
        return $this->store($definition)->list($definition, $query);
    }

    public function store(EntityDefinition $definition): ObjectStoreInterface
    {
        foreach ($this->stores as $store) {
            if ($store->supports($definition)) {
                return $store;
            }
        }

        throw new \LogicException(sprintf('No store holds the objects of "%s".', $definition->name()));
    }
}
