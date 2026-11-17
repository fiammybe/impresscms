<?php

declare(strict_types=1);

namespace App\Ipf\Table;

/**
 * A list, resolved and ready for a template (design D5): captions, cell HTML
 * and the links for sorting and paging.
 */
final class TableView
{
    /**
     * @param list<array{field: string, caption: string, align: ?string, width: int|string|null, sortUrl: ?string, sorted: ?string}> $columns
     * @param list<array{id: int|string, cells: list<string>, actions: list<string>}>                                           $rows    cells and actions are HTML
     * @param list<array{name: string, location: string, caption: string}>                                                      $introButtons
     * @param array{name: string, value: string, caption: string}|null                                                                          $quickSearch
     */
    public function __construct(
        public readonly string $id,
        public readonly array $columns,
        public readonly array $rows,
        public readonly int $total,
        public readonly int $page,
        public readonly int $pages,
        public readonly ?string $previousUrl,
        public readonly ?string $nextUrl,
        public readonly string $formAction,
        public readonly array $introButtons = [],
        public readonly ?array $quickSearch = null,
        public readonly bool $hasActions = false,
    ) {
    }
}
