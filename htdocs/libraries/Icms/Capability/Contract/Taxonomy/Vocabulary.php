<?php

/**
 * A taxonomy vocabulary, as the taxonomy contract exposes it
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		ICMS\Capability
 */

declare(strict_types=1);

namespace Icms\Capability\Contract\Taxonomy;

final class Vocabulary
{
    /**
     * @param list<string> $objectTypes the object types it applies to; empty for all
     */
    public function __construct(
        public readonly string $id,
        public readonly string $label,
        public readonly bool $multiple,
        public readonly array $objectTypes = [],
    ) {
    }

    public function appliesTo(string $objectType): bool
    {
        if ($this->objectTypes === []) {
            return true;
        }

        return in_array($objectType, $this->objectTypes, true);
    }
}
