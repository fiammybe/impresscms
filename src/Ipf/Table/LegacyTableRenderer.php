<?php

declare(strict_types=1);

namespace App\Ipf\Table;

use Icms\Db\Criteria\Compo;
use Icms\Ipf\View\Column;
use Icms\Ipf\View\Table;
use Symfony\Component\HttpFoundation\Request;

/**
 * Renders a list with Icms\Ipf\View\Table (design D5): the same HTML, cookies
 * and Smarty template as a legacy page, so themes that override
 * system_persistabletable_display.html keep working.
 *
 * The legacy table reads the request from the superglobals, which the host
 * mirrors; the request argument is not used.
 */
final class LegacyTableRenderer implements TableRendererInterface
{
    public function render(TableConfig $table, Request $request): string
    {
        $handler = $table->handler;
        if ($handler === null) {
            throw new \LogicException(sprintf('The legacy table needs an IPF handler, which "%s" does not have.', $table->definition->name()));
        }

        $legacy = new Table($handler, new Compo(), $table->actions());
        foreach ($table->columns() as $column) {
            $legacy->addColumn(new Column(
                $column->field,
                $column->align ?? _GLOBAL_LEFT,
                $column->width ?? false,
                $column->customMethod ?? false,
                $column->param ?? false,
                $column->caption ?? false,
                $column->sortable,
            ));
        }

        foreach ($table->introButtons() as $button) {
            $legacy->addIntroButton($button['name'], $button['location'], $button['caption']);
        }

        if ($table->quickSearch() !== []) {
            $legacy->addQuickSearch($table->quickSearch());
        }

        if ($table->defaultSort() !== null) {
            $legacy->setDefaultSort($table->defaultSort());
            // what setDefaultOrder() does; its documented parameter type does not exist
            $legacy->_ordersel = $table->defaultOrder();
        }

        return (string) $legacy->fetch();
    }
}
