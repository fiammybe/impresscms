<?php

declare(strict_types=1);

namespace App\Ipf\Table;

use Symfony\Component\HttpFoundation\Request;

/**
 * Turns a list into HTML (design D5). A page picks its renderer:
 * LegacyTableRenderer for the output of Icms\Ipf\View\Table, TwigTableRenderer
 * for the Twig table.
 */
interface TableRendererInterface
{
    public function render(TableConfig $table, Request $request): string;
}
