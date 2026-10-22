<?php

/**
 * A taxonomy term, as the taxonomy contract exposes it
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		ICMS\Capability
 */

declare(strict_types=1);

namespace Icms\Capability\Contract\Taxonomy;

final class Term
{
    public function __construct(
        public readonly string $id,
        public readonly string $vocabularyId,
        public readonly string $label,
        public readonly string $slug,
        public readonly ?string $parentId = null,
    ) {
    }
}
