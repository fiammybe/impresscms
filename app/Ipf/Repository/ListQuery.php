<?php

declare(strict_types=1);

namespace App\Ipf\Repository;

use App\Ipf\Resource\ResourceDescriptor;
use Illuminate\Http\Request;

/**
 * What an admin list shows: quick search, sort, order and page, read from the
 * parameters and cookies Icms\Ipf\View\Table uses, so its links and bookmarks
 * keep working:
 *
 * - `<item>_sortsel`, `<item>_ordersel` (cookies tbl_<Entity>_sortsel, …)
 * - `limitsel` (cookie tbl_limitsel; `all` for no limit), `start<key>`
 * - `quicksearch_<Entity>` (POST or query string)
 *
 * The sort column must be one of the list's sortable columns, the key or the
 * identifier; anything else falls back to the default.
 */
final class ListQuery
{
    public function __construct(
        public readonly ?string $search,
        public readonly string $sort,
        public readonly string $order,
        public readonly ?int $limit,
        public readonly int $start,
    ) {
    }

    /**
     * @param class-string<ResourceDescriptor> $descriptor
     */
    public static function fromRequest(string $descriptor, Request $request): self
    {
        $list = $descriptor::listView() ?? ['columns' => [], 'sortBy' => $descriptor::identifier(), 'order' => 'ASC', 'limit' => 15];
        $item = $descriptor::item();
        $entity = $descriptor::entityClass();

        $sortable = [$descriptor::primaryKey(), $descriptor::identifier()];
        foreach ($list['columns'] as $column) {
            if ($column['sortable']) {
                $sortable[] = $column['field'];
            }
        }

        $sort = (string) $request->query->get("{$item}_sortsel", (string) $request->cookies->get("tbl_{$entity}_sortsel", $list['sortBy']));
        if (! in_array($sort, $sortable, true)) {
            $sort = (string) $list['sortBy'];
        }

        $order = strtoupper((string) $request->query->get("{$item}_ordersel", (string) $request->cookies->get("tbl_{$entity}_ordersel", $list['order'])));
        $limit = (string) $request->query->get('limitsel', (string) $request->cookies->get('tbl_limitsel', (string) $list['limit']));
        $search = trim((string) $request->request->get("quicksearch_{$entity}", (string) $request->query->get("quicksearch_{$entity}", '')));

        return new self(
            search: $search !== '' ? $search : null,
            sort: $sort,
            order: $order === 'DESC' ? 'DESC' : 'ASC',
            limit: $limit === 'all' ? null : max(1, (int) $limit ?: (int) $list['limit']),
            start: max(0, (int) $request->query->get('start' . $descriptor::primaryKey(), 0)),
        );
    }
}
