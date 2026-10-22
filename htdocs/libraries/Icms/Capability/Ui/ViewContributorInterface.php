<?php

/**
 * A provider that adds rows to the generic IPF single view
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		ICMS\Capability
 */

declare(strict_types=1);

namespace Icms\Capability\Ui;

use Icms\Capability\ObjectRef;

interface ViewContributorInterface
{
    /**
     * Load the data of these objects at once, before viewRow() is asked for them.
     *
     * @param list<ObjectRef> $refs
     */
    public function prefetch(array $refs): void;

    /**
     * The row for the object, with escaped HTML, or null for no row.
     *
     * @return array{
        caption: string,
        html: string
    }|null
     */
    public function viewRow(ObjectRef $ref): ?array;
}
