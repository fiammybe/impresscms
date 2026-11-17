<?php

declare(strict_types=1);

namespace App\Ipf\Store;

use App\Ipf\Metadata\EntityDefinition;
use App\Ipf\Query\ListQuery;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Where the objects of a description live (design D4). An object belongs to
 * exactly one store.
 */
#[AutoconfigureTag('icms.ipf.object_store')]
interface ObjectStoreInterface
{
    public function supports(EntityDefinition $definition): bool;

    public function find(EntityDefinition $definition, int|string $id): ?object;

    public function list(EntityDefinition $definition, ListQuery $query): ListResult;

    /**
     * The typed, unformatted value of a field: numbers as numbers, lists as
     * lists, text as stored.
     */
    public function value(EntityDefinition $definition, object $object, string $field): mixed;

    /**
     * The value of a field as HTML, ready to show in a list.
     */
    public function display(EntityDefinition $definition, object $object, string $field): string;

    /**
     * The address of the object's own page, when it has one.
     */
    public function url(EntityDefinition $definition, object $object): ?string;
}
