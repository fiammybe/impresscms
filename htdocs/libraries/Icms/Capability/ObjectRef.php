<?php

/**
 * Provider-neutral reference to an IPF object
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		ICMS\Capability
 */

declare(strict_types=1);

namespace Icms\Capability;

use Icms\Capability\Exception\UnsupportedObjectException;
use Icms\Db\Criteria\Item;
use Icms\Ipf\Entity;
use Icms\Ipf\Registry\Handler as ObjectCache;
use InvalidArgumentException;

/**
 * The only coupling between capability providers and consumer objects: the
 * owning module, the IPF item and the key value (design D4). Providers store
 * these triples, never foreign keys into consumer tables.
 */
final class ObjectRef
{
    /**
     * Ids per object type that were loaded through the IPF object cache in this
     * request: id => whether the object exists.
     *
     * @var array<string, array<int, bool>>
     */
    private static array $resolved = [];

    public function __construct(
        public readonly string $module,
        public readonly string $item,
        public readonly int $id,
    ) {
        if ($module === '' || $item === '') {
            throw new InvalidArgumentException('An object reference needs a module and an item.');
        }

        if ($id < 1) {
            throw new InvalidArgumentException("An object reference needs a positive id, got {$id}.");
        }
    }

    public static function fromEntity(object $entity): self
    {
        if (! $entity instanceof Entity) {
            throw UnsupportedObjectException::forObject($entity);
        }

        if (is_array($entity->handler->keyName)) {
            throw UnsupportedObjectException::compositeKey($entity);
        }

        $id = (int) $entity->id();

        if ($id === 0) {
            throw UnsupportedObjectException::notStored($entity);
        }

        return new self($entity->handler->_moduleName, $entity->handler->_itemname, $id);
    }

    /** Parse "module/item/id", as produced by toString(). */
    public static function fromString(string $reference): self
    {
        $parts = explode('/', $reference);

        if (count($parts) !== 3) {
            throw new InvalidArgumentException("\"{$reference}\" is not an object reference (module/item/id).");
        }

        return new self($parts[0], $parts[1], (int) $parts[2]);
    }

    /** The object type of an entity ("news/article"), also for an unsaved one. */
    public static function typeOf(object $entity): string
    {
        if (! $entity instanceof Entity) {
            throw UnsupportedObjectException::forObject($entity);
        }

        return "{$entity->handler->_moduleName}/{$entity->handler->_itemname}";
    }

    public function objectType(): string
    {
        return "{$this->module}/{$this->item}";
    }

    public function toString(): string
    {
        return "{$this->module}/{$this->item}/{$this->id}";
    }

    public function equals(self $other): bool
    {
        return $this->toString() === $other->toString();
    }

    public function resolve(): ?Entity
    {
        return self::resolveMany([$this])[$this->toString()] ?? null;
    }

    /**
     * Load the IPF objects behind the references, one query per object type
     * at most, through the per-request IPF object cache.
     *
     * @param list<ObjectRef> $refs
     * @return array<string, Entity> keyed by ObjectRef::toString(); unresolvable refs are left out
     */
    public static function resolveMany(array $refs): array
    {
        $idsByType = [];

        foreach ($refs as $ref) {
            $idsByType[$ref->objectType()][$ref->id] = $ref;
        }

        $objects = [];

        foreach ($idsByType as $refsById) {
            $first = reset($refsById);

            foreach (self::resolveType($first->module, $first->item, array_keys($refsById)) as $id => $object) {
                $objects[$refsById[$id]->toString()] = $object;
            }
        }

        return $objects;
    }

    /** Forget which objects were resolved, for a new request in the same process (tests). */
    public static function reset(): void
    {
        self::$resolved = [];
    }

    /**
     * @param list<int> $ids
     * @return array<int, Entity>
     */
    private static function resolveType(string $module, string $item, array $ids): array
    {
        if (! Registry::instance()->isModuleActive($module)) {
            return [];
        }

        $handler = icms_getModuleHandler($item, $module, null, true);

        if (! is_object($handler)) {
            return [];
        }

        $type = "{$module}/{$item}";
        $known = self::$resolved[$type] ?? [];
        $missing = array_values(array_diff($ids, array_keys($known)));
        $cache = ObjectCache::getInstance();

        if ($missing !== []) {
            $load = array_merge(array_keys(array_filter($known)), $missing);
            $loaded = $cache->addObjectsFromHandler($handler, new Item($handler->keyName, '(' . implode(',', $load) . ')', 'IN'));
            $loaded = is_array($loaded) ? $loaded : [];

            foreach ($load as $id) {
                $known[$id] = isset($loaded[$id]);
            }

            self::$resolved[$type] = $known;
        }

        $objects = [];

        foreach ($ids as $id) {
            if (! $known[$id]) {
                continue;
            }

            $object = $cache->getSingleObject($item, $id, $module);

            if ($object instanceof Entity) {
                $objects[$id] = $object;
            }
        }

        return $objects;
    }
}
