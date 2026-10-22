<?php

/**
 * A provider that adds fields to IPF edit forms
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		ICMS\Capability
 */

declare(strict_types=1);

namespace Icms\Capability\Ui;

use Icms\Capability\ObjectRef;
use Icms\Form\Element;

interface FormContributorInterface
{
    /**
     * The fields of the capability's form section. Every field is named under
     * the prefix ("capability[tagging]" → "capability[tagging][tags]") and is
     * prefilled from the provider's data when $ref is not null.
     *
     * @param ObjectRef|null $ref null for a new object
     * @return list<Element>
     */
    public function formElements(string $objectType, ?ObjectRef $ref, string $fieldPrefix): array;
}
