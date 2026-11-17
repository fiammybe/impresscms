<?php

declare(strict_types=1);

namespace App\Ipf\Table;

use App\Ipf\IpfRepository;
use App\Ipf\Query\ListQuery;
use App\Ipf\Query\ListQueryResolver;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

/**
 * Renders a list with Twig (design D5), from the list pipeline: the request's
 * options (in the parameter names of the legacy table) become a ListQuery, the
 * object's store answers it, and templates/ipf/table.html.twig shows it.
 *
 * Unlike the legacy table it keeps no cookies, and sorting, paging and the
 * quick search are plain GET links.
 */
final class TwigTableRenderer implements TableRendererInterface
{
    public const TEMPLATE = 'ipf/table.html.twig';

    public function __construct(
        private readonly ListQueryResolver $queries,
        private readonly IpfRepository $repository,
        private readonly Environment $twig,
    ) {
    }

    public function render(TableConfig $table, Request $request): string
    {
        return $this->twig->render(self::TEMPLATE, ['table' => $this->view($table, $request)]);
    }

    public function view(TableConfig $table, Request $request): TableView
    {
        $definition = $table->definition;
        $query = $this->queries->fromTableRequest(
            $request,
            $definition,
            $table->id(),
            $table->quickSearch(),
            $table->defaultSort(),
            $table->defaultOrder(),
        );
        $result = $this->repository->list($definition, $query);
        $store = $this->repository->store($definition);

        $rows = [];
        foreach ($result->items as $object) {
            $cells = [];
            foreach ($table->columns() as $column) {
                $cells[] = $column->customMethod !== null
                    ? (string) $object->{$column->customMethod}(...($column->param !== null ? [$column->param] : []))
                    : $store->display($definition, $object, $column->field);
            }

            $rows[] = [
                'id' => $store->value($definition, $object, $definition->keyField),
                'cells' => $cells,
                'actions' => self::actions($table, $object),
            ];
        }

        $item = $definition->item;
        $columns = [];
        foreach ($table->columns() as $column) {
            $sortable = $column->sortable && $definition->isQueryable($column->field);
            $sorted = $query->sort === $column->field ? $query->order : null;
            $columns[] = [
                'field' => $column->field,
                'caption' => $table->columnCaption($column),
                'align' => $column->align,
                'width' => $column->width,
                'sortUrl' => $sortable ? self::url($request, [
                    "{$item}_sortsel" => $column->field,
                    "{$item}_ordersel" => $sorted === ListQuery::ASC ? ListQuery::DESC : ListQuery::ASC,
                    "start{$definition->keyField}" => null,
                ]) : null,
                'sorted' => $sorted,
            ];
        }

        $start = "start{$definition->keyField}";
        $pages = $query->limit > 0 ? max(1, (int) ceil($result->total / $query->limit)) : 1;
        $page = min($query->page(), $pages);
        $searchName = "quicksearch_{$table->id()}";

        return new TableView(
            id: $table->id(),
            columns: $columns,
            rows: $rows,
            total: $result->total,
            page: $page,
            pages: $pages,
            previousUrl: $page > 1 ? self::url($request, [$start => ($page - 2) * $query->limit]) : null,
            nextUrl: $page < $pages ? self::url($request, [$start => $page * $query->limit]) : null,
            formAction: self::url($request, [$start => null, $searchName => null]),
            introButtons: $table->introButtons(),
            quickSearch: $table->quickSearch() !== [] ? [
                'name' => $searchName,
                'value' => $query->search,
                // the system module's caption, when its language file is loaded
                'caption' => defined('_CO_ICMS_QUICK_SEARCH') ? _CO_ICMS_QUICK_SEARCH : _SEARCH,
            ] : null,
            hasActions: $table->handler !== null && $table->actions() !== [],
        );
    }

    /**
     * The edit and delete links of an IPF object, as the legacy table shows
     * them. Objects without a handler have no actions yet.
     *
     * @return list<string>
     */
    private static function actions(TableConfig $table, object $object): array
    {
        if ($table->handler === null) {
            return [];
        }

        $actions = [];
        if (in_array('edit', $table->actions(), true)) {
            $actions[] = (string) $object->getEditItemLink(false, true, false);
        }

        if (in_array('delete', $table->actions(), true)) {
            $actions[] = (string) $object->getDeleteItemLink(false, true, false);
        }

        return $actions;
    }

    /**
     * The current address with some query parameters replaced; null removes one.
     *
     * @param array<string, int|string|null> $replace
     */
    private static function url(Request $request, array $replace): string
    {
        $parameters = array_filter(
            array_replace($request->query->all(), $replace),
            static fn (mixed $value): bool => $value !== null,
        );
        $path = $request->getBaseUrl() . $request->getPathInfo();
        if ($parameters === []) {
            return $path;
        }

        return $path . '?' . http_build_query($parameters);
    }
}
