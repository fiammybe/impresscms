<?php

/**
 * A provider that offers a filter in IPF admin tables
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		ICMS\Capability
 */

declare(strict_types=1);

namespace Icms\Capability\Ui;

interface TableFilterContributorInterface
{
    /** @return array<string, string> filter value => label; empty for no filter */
    public function filterOptions(string $objectType): array;

    /** @return list<int> the ids of the objects of the type that match the filter value */
    public function matchingIds(string $objectType, string $filterValue): array;
}
