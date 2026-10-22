<?php

/**
 * A provider that stores the fields it added to an IPF form
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		ICMS\Capability
 */

declare(strict_types=1);

namespace Icms\Capability\Ui;

use Icms\Capability\ObjectRef;
use Icms\Capability\Submission;

interface PersistenceContributorInterface
{
    /**
     * Store the submitted values for an object that has just been stored.
     * Providers validate leniently and store what is valid.
     *
     * @return list<string> error messages; empty on success
     */
    public function store(ObjectRef $ref, Submission $submission): array;
}
