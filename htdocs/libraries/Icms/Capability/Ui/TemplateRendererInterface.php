<?php

/**
 * A provider that renders output for templates
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		ICMS\Capability
 */

declare(strict_types=1);

namespace Icms\Capability\Ui;

use Icms\Capability\ObjectRef;

interface TemplateRendererInterface
{
    /**
     * HTML for the object in a display variant the provider defines ("list",
     * "cloud", …); an empty string for an unknown variant.
     *
     * @param array<string, mixed> $params the other parameters of the template tag
     */
    public function render(ObjectRef $ref, string $variant, array $params): string;
}
