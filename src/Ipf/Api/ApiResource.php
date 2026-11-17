<?php

declare(strict_types=1);

namespace App\Ipf\Api;

/**
 * How a module exposes one of its objects through the API (design D6), from
 * `$modversion['object_api'][<item>]` in its manifest.
 */
final class ApiResource
{
    /**
     * @param string            $read   the security attribute reading requires
     * @param list<string>      $search the fields `q` looks in; empty for the identifier
     * @param list<string>|null $fields the fields returned; null for every stored field
     */
    public function __construct(
        public readonly string $module,
        public readonly string $item,
        public readonly string $read,
        public readonly array $search = [],
        public readonly ?array $fields = null,
    ) {
    }
}
