<?php

declare(strict_types=1);

namespace App\Ipf\Definition;

/**
 * The admin list of a resource (Icms\Ipf\Metadata\ListView, ListColumn and
 * ListFilter): what an icms_ipf_view_Table shows, searches and sorts.
 */
final class ListDefinition
{
    /**
     * @param list<array{field: string, align: string, width: ?int, sortable: bool}> $columns
     * @param list<string> $quickSearch
     * @param list<array{field: string, method: string, default: ?string}> $filters
     * @param list<string> $actions
     */
    public function __construct(
        public readonly array $columns,
        public readonly array $quickSearch,
        public readonly array $filters,
        public readonly array $actions,
        public readonly bool $createButton,
        public readonly string $sortBy,
        public readonly string $order,
        public readonly int $limit,
    ) {
    }
}
