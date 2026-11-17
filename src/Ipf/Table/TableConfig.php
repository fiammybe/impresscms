<?php

declare(strict_types=1);

namespace App\Ipf\Table;

use App\Ipf\Metadata\EntityDefinition;
use App\Ipf\Query\ListQuery;

/**
 * What a list of an object shows (design D5): columns, quick search, buttons
 * and actions. TableFactory fills it from the object's description; a page
 * changes what it wants different. Every renderer reads the same config.
 */
final class TableConfig
{
    /** @var list<ColumnConfig> */
    private array $columns = [];

    /** @var list<string> */
    private array $quickSearch = [];

    /** @var list<array{name: string, location: string, caption: string}> */
    private array $introButtons = [];

    /** @var list<string> */
    private array $actions = ['edit', 'delete'];

    private ?string $defaultSort = null;
    private string $defaultOrder = ListQuery::ASC;

    /**
     * @param object|null $handler the IPF handler, for objects that have one
     */
    public function __construct(
        public readonly EntityDefinition $definition,
        public readonly ?object $handler = null,
    ) {
    }

    /**
     * The id that names the table's quick search field and cookies; for an
     * IPF object the one Icms\Ipf\View\Table uses.
     */
    public function id(): string
    {
        return $this->definition->className !== '' ? $this->definition->className : $this->definition->item;
    }

    public function addColumn(
        string $field,
        ?string $align = null,
        int|string|null $width = null,
        ?string $customMethod = null,
        mixed $param = null,
        ?string $caption = null,
        bool $sortable = true,
    ): static {
        $this->columns[] = new ColumnConfig($field, $align, $width, $customMethod, $param, $caption, $sortable);

        return $this;
    }

    /**
     * Replace the columns, for a page that does not want the default ones.
     */
    public function clearColumns(): static
    {
        $this->columns = [];

        return $this;
    }

    /**
     * @return list<ColumnConfig>
     */
    public function columns(): array
    {
        return $this->columns;
    }

    /**
     * @param list<string> $fields the fields the quick search looks in; none for no quick search
     */
    public function setQuickSearch(array $fields): static
    {
        $this->quickSearch = array_values($fields);

        return $this;
    }

    /**
     * @return list<string>
     */
    public function quickSearch(): array
    {
        return $this->quickSearch;
    }

    public function addIntroButton(string $name, string $location, string $caption): static
    {
        $this->introButtons[] = ['name' => $name, 'location' => $location, 'caption' => $caption];

        return $this;
    }

    /**
     * @return list<array{name: string, location: string, caption: string}>
     */
    public function introButtons(): array
    {
        return $this->introButtons;
    }

    /**
     * @param list<string> $actions of "edit" and "delete"
     */
    public function setActions(array $actions): static
    {
        $this->actions = array_values($actions);

        return $this;
    }

    /**
     * @return list<string>
     */
    public function actions(): array
    {
        return $this->actions;
    }

    public function setDefaultSort(?string $field, string $order = ListQuery::ASC): static
    {
        $this->defaultSort = $field;
        $this->defaultOrder = $order;

        return $this;
    }

    public function defaultSort(): ?string
    {
        return $this->defaultSort;
    }

    public function defaultOrder(): string
    {
        return $this->defaultOrder;
    }

    public function columnCaption(ColumnConfig $column): string
    {
        if ($column->caption !== null) {
            return $column->caption;
        }

        if (!$this->definition->hasField($column->field)) {
            return $column->field;
        }

        return $this->definition->field($column->field)->caption;
    }
}
