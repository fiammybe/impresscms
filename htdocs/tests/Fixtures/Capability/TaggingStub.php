<?php

/**
 * A tagging provider that stores nothing, for discovery tests
 *
 * @package icms\tests
 */

declare(strict_types=1);

namespace Icms\Tests\Fixtures\Capability;

use Icms\Capability\Contract\Tagging\CapabilityInterface;
use Icms\Capability\ObjectRef;

/**
 * Fixture provider classes (mod_<dirname>_TaggingCapability) extend this; the
 * provider module is read from the class name.
 */
abstract class TaggingStub implements CapabilityInterface
{
    public const CONTRACT_VERSION = '1.0';

    public function capabilityName(): string
    {
        return self::NAME;
    }

    public function contractVersion(): string
    {
        return static::CONTRACT_VERSION;
    }

    public function providerModule(): string
    {
        return explode('_', static::class)[1];
    }

    public function supportsObjectType(string $objectType): bool
    {
        return true;
    }

    public function clear(ObjectRef $ref): void
    {
    }

    public function clearType(string $objectType): void
    {
    }

    public function adminUrl(): ?string
    {
        return null;
    }

    public function setTags(ObjectRef $ref, array $labels): void
    {
    }

    public function addTags(ObjectRef $ref, array $labels): void
    {
    }

    public function removeTags(ObjectRef $ref, array $labels): void
    {
    }

    public function getTags(ObjectRef $ref): array
    {
        return [];
    }

    public function getTagsForObjects(array $refs): array
    {
        return array_fill_keys(array_map(static fn (ObjectRef $ref): string => $ref->toString(), $refs), []);
    }

    public function findObjects(string $tagSlug, ?string $objectType = null, int $limit = 20, int $start = 0): array
    {
        return [];
    }

    public function suggest(string $prefix, int $limit = 10): array
    {
        return [];
    }

    public function cloud(?string $objectType = null, int $limit = 50): array
    {
        return [];
    }
}
