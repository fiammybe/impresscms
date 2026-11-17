<?php

declare(strict_types=1);

namespace App\Ipf\Store;

use App\Ipf\Metadata\EntityDefinition;
use App\Ipf\Query\InvalidListQueryException;
use App\Ipf\Query\ListQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Objects of ORM-mapped classes (design D4, D7): the list options become an
 * ORM query with bound parameters. Read-only for now.
 */
final class DoctrineStore implements ObjectStoreInterface
{
    public function __construct(private readonly ManagerRegistry $doctrine)
    {
    }

    public function supports(EntityDefinition $definition): bool
    {
        return $definition->source === EntityDefinition::SOURCE_ORM;
    }

    public function find(EntityDefinition $definition, int|string $id): ?object
    {
        return $this->manager($definition)->find(self::entityClass($definition), $id);
    }

    public function list(EntityDefinition $definition, ListQuery $query): ListResult
    {
        $builder = $this->manager($definition)->createQueryBuilder()
            ->select('o')
            ->from(self::entityClass($definition), 'o');

        $index = 0;
        foreach ($query->filters as $field => $value) {
            self::assertQueryable($definition, $field);
            $builder->andWhere("o.{$field} = :filter{$index}")->setParameter("filter{$index}", $value);
            ++$index;
        }

        if ($query->hasSearch()) {
            $conditions = [];
            foreach ($query->searchFields as $field) {
                self::assertQueryable($definition, $field);
                $conditions[] = "o.{$field} LIKE :search";
            }

            $builder->andWhere(implode(' OR ', $conditions))->setParameter('search', "%{$query->search}%");
        }

        if ($query->sort !== null) {
            self::assertQueryable($definition, $query->sort);
            $builder->orderBy("o.{$query->sort}", $query->order === ListQuery::DESC ? 'DESC' : 'ASC');
        }

        $builder->setFirstResult($query->offset);
        if ($query->limit > 0) {
            $builder->setMaxResults($query->limit);
        }

        // no joins: the page can be fetched without the paginator's sub-queries
        $page = new Paginator($builder, false);

        return new ListResult(array_values(iterator_to_array($page)), count($page));
    }

    public function value(EntityDefinition $definition, object $object, string $field): mixed
    {
        $value = $this->manager($definition)->getClassMetadata(self::entityClass($definition))->getFieldValue($object, $field);
        if (is_string($value)) {
            // a table shared with legacy code may hold filtered text
            return StoredText::withoutFilterMarkers($value);
        }

        return $value;
    }

    public function display(EntityDefinition $definition, object $object, string $field): string
    {
        $value = $this->value($definition, $object, $field);
        if (is_array($value)) {
            $value = implode(', ', array_map('strval', $value));
        }

        return htmlspecialchars(is_scalar($value) ? (string) $value : '', ENT_QUOTES);
    }

    public function url(EntityDefinition $definition, object $object): ?string
    {
        return null;
    }

    /**
     * Field names go into the query text: only fields of the description do.
     */
    private static function assertQueryable(EntityDefinition $definition, string $field): void
    {
        if (!$definition->isQueryable($field)) {
            throw InvalidListQueryException::unknownField($definition->name(), 'query', $field);
        }
    }

    private function manager(EntityDefinition $definition): EntityManagerInterface
    {
        $class = self::entityClass($definition);
        $manager = $this->doctrine->getManagerForClass($class);
        if (!$manager instanceof EntityManagerInterface) {
            throw new \LogicException(sprintf('No entity manager maps "%s".', $class));
        }

        return $manager;
    }

    /**
     * @return class-string
     */
    private static function entityClass(EntityDefinition $definition): string
    {
        if (!class_exists($definition->className)) {
            throw new \LogicException(sprintf('The class "%s" of "%s" does not exist.', $definition->className, $definition->name()));
        }

        return $definition->className;
    }
}
