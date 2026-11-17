<?php

declare(strict_types=1);

namespace App\Ipf\Api;

use App\Ipf\Metadata\EntityDefinition;

/**
 * One object on its way out of the API: the serializer turns it into its
 * exposed fields (EntityNormalizer).
 */
final class ApiItem
{
    public function __construct(
        public readonly EntityDefinition $definition,
        public readonly ApiResource $resource,
        public readonly object $object,
    ) {
    }
}
