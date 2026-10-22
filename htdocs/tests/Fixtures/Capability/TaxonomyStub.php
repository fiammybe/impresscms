<?php

/**
 * A taxonomy provider that stores nothing, for discovery tests
 *
 * @package icms\tests
 */

declare(strict_types=1);

namespace Icms\Tests\Fixtures\Capability;

use Icms\Capability\Contract\Taxonomy\CapabilityInterface;
use Icms\Capability\ObjectRef;

abstract class TaxonomyStub implements CapabilityInterface
{
    public function capabilityName(): string
    {
        return self::NAME;
    }

    public function contractVersion(): string
    {
        return '1.0';
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

    public function vocabularies(?string $objectType = null): array
    {
        return [];
    }

    public function terms(string $vocabularyId): array
    {
        return [];
    }

    public function classify(ObjectRef $ref, array $termIds): void
    {
    }

    public function unclassify(ObjectRef $ref, array $termIds): void
    {
    }

    public function termsOf(ObjectRef $ref): array
    {
        return [];
    }

    public function termsOfObjects(array $refs): array
    {
        return [];
    }

    public function findObjects(string $termId, bool $includeDescendants = true, ?string $objectType = null, int $limit = 20, int $start = 0): array
    {
        return [];
    }
}
