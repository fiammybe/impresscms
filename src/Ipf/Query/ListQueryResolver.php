<?php

declare(strict_types=1);

namespace App\Ipf\Query;

use App\Ipf\Metadata\EntityDefinition;
use Symfony\Component\HttpFoundation\Request;

/**
 * Reads a ListQuery from a request (design D3), in one of two dialects:
 *
 * - table: the parameter names of Icms\Ipf\View\Table (`<item>_sortsel`,
 *   `<item>_ordersel`, `start<key>`, `limitsel`, `filtersel`, `filtersel2`,
 *   `quicksearch_<table id>`), so existing links and forms keep working;
 * - api: `q`, `sort`, `order`, `page`, `limit`, `filter[<field>]`.
 *
 * Every field named by the request is checked against the description: only
 * fields stored in the table can be sorted, filtered or searched.
 */
final class ListQueryResolver
{
    public const DEFAULT_LIMIT = 15;
    public const MAX_LIMIT = 100;

    /**
     * @param string       $tableId      the table's id, which names its quick search field
     * @param list<string> $searchFields the fields the quick search covers
     *
     * @throws InvalidListQueryException
     */
    public function fromTableRequest(
        Request $request,
        EntityDefinition $definition,
        string $tableId,
        array $searchFields = [],
        ?string $defaultSort = null,
        string $defaultOrder = ListQuery::ASC,
    ): ListQuery {
        $query = $request->query;
        $post = $request->request;
        $item = $definition->item;

        // the table posts its limit, filter and quick search forms
        $limit = (int) $post->get('limitsel', $query->get('limitsel', 0));
        $search = (string) $post->get("quicksearch_{$tableId}", $query->get("quicksearch_{$tableId}", ''));
        $filterField = (string) $post->get('filtersel', $query->get('filtersel', 'default'));
        $filterValue = (string) $post->get('filtersel2', $query->get('filtersel2', 'default'));

        $filters = [];
        if ($filterField !== 'default' && $filterField !== '' && $filterValue !== 'default') {
            $filters[$filterField] = $filterValue;
        }

        return $this->build(
            $definition,
            (string) $query->get("{$item}_sortsel", '') ?: ($defaultSort ?? $definition->identifierField),
            (string) $query->get("{$item}_ordersel", '') ?: $defaultOrder,
            max(0, (int) $query->get("start{$definition->keyField}", 0)),
            $limit > 0 ? $limit : self::DEFAULT_LIMIT,
            $search,
            $searchFields,
            $filters,
        );
    }

    /**
     * @param list<string> $searchFields the fields `q` covers
     *
     * @throws InvalidListQueryException
     */
    public function fromApiRequest(Request $request, EntityDefinition $definition, array $searchFields = []): ListQuery
    {
        $query = $request->query;
        $limit = $query->has('limit') ? $query->getInt('limit') : self::DEFAULT_LIMIT;
        if ($limit < 1) {
            throw new InvalidListQueryException('The limit must be at least 1.');
        }

        $limit = min($limit, self::MAX_LIMIT);
        $page = $query->has('page') ? $query->getInt('page') : 1;
        if ($page < 1) {
            throw new InvalidListQueryException('The page must be at least 1.');
        }

        $filters = [];
        foreach ($query->all()['filter'] ?? [] as $field => $value) {
            if (!is_scalar($value)) {
                throw new InvalidListQueryException(sprintf('The filter on "%s" needs a single value.', $field));
            }

            $filters[(string) $field] = (string) $value;
        }

        return $this->build(
            $definition,
            (string) $query->get('sort', '') ?: $definition->identifierField,
            (string) $query->get('order', '') ?: ListQuery::ASC,
            ($page - 1) * $limit,
            $limit,
            trim((string) $query->get('q', '')),
            $searchFields,
            $filters,
        );
    }

    /**
     * @param list<string>              $searchFields
     * @param array<string, int|string> $filters
     */
    private function build(
        EntityDefinition $definition,
        string $sort,
        string $order,
        int $offset,
        int $limit,
        string $search,
        array $searchFields,
        array $filters,
    ): ListQuery {
        $name = $definition->name();
        if (!$definition->isQueryable($sort) || !$definition->field($sort)->sortable) {
            throw InvalidListQueryException::unknownField($name, 'sort', $sort);
        }

        $order = strtoupper($order);
        if ($order !== ListQuery::ASC && $order !== ListQuery::DESC) {
            throw new InvalidListQueryException(sprintf('The sort order must be ASC or DESC, not "%s".', $order));
        }

        foreach ($searchFields as $field) {
            if (!$definition->isQueryable($field)) {
                throw InvalidListQueryException::unknownField($name, 'search', $field);
            }
        }

        foreach (array_keys($filters) as $field) {
            if (!$definition->isQueryable($field)) {
                throw InvalidListQueryException::unknownField($name, 'filter', $field);
            }
        }

        return new ListQuery($sort, $order, $offset, $limit, $search, array_values($searchFields), $filters);
    }
}
