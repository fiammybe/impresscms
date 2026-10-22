<?php

/**
 * The heading of a capability's section in an IPF form
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		ICMS\Capability
 */

declare(strict_types=1);

namespace Icms\Capability;

use Icms\Ipf\Form\Elements\Section;

/**
 * Rendered by Icms\Ipf\Form\Base like the sections an entity opens with
 * openFormSection(), but it belongs to no entity variable.
 */
final class FormSection extends Section
{
    public function __construct(
        private readonly string $capability,
        private readonly string $caption,
    ) {
        $this->setName("capability_{$capability}_section");
    }

    public function capability(): string
    {
        return $this->capability;
    }

    public function getValue()
    {
        return htmlspecialchars($this->caption, ENT_QUOTES);
    }

    public function isClosingSection()
    {
        return false;
    }

    public function render(): string
    {
        return $this->getValue();
    }
}
