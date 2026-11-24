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

        $query = $request->query->all();
        $cookies = $request->cookies->all();
        $sort = self::scalar($query, "{$item}_sortsel") ?? self::scalar($cookies, "tbl_{$entity}_sortsel") ?? (string) $list['sortBy'];
        if (! in_array($sort, $sortable, true)) {
            $sort = (string) $list['sortBy'];
        }

        $order = strtoupper(self::scalar($query, "{$item}_ordersel") ?? self::scalar($cookies, "tbl_{$entity}_ordersel") ?? (string) $list['order']);
        $limit = self::scalar($query, 'limitsel') ?? self::scalar($cookies, 'tbl_limitsel') ?? (string) $list['limit'];
        $search = trim(self::scalar($request->request->all(), "quicksearch_{$entity}") ?? self::scalar($query, "quicksearch_{$entity}") ?? '');

        return new self(
            search: $search !== '' ? $search : null,
            sort: $sort,
            order: $order === 'DESC' ? 'DESC' : 'ASC',
            limit: $limit === 'all' ? null : max(1, (int) $limit ?: (int) $list['limit']),
            start: max(0, (int) (self::scalar($query, 'start' . $descriptor::primaryKey()) ?? 0)),
        );
    }

    /**
     * A scalar parameter as a string; null when missing or not scalar
     * (InputBag::get() refuses arrays, which the legacy table ignores).
     *
     * @param array<string, mixed> $parameters
     */
    private static function scalar(array $parameters, string $name): ?string
    {
        $value = $parameters[$name] ?? null;

        return is_scalar($value) ? (string) $value : null;
    }
}
