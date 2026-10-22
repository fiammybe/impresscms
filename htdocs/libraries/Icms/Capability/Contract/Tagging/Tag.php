<?php

/**
 * A tag, as the tagging contract exposes it
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		ICMS\Capability
 */

declare(strict_types=1);

namespace Icms\Capability\Contract\Tagging;

final class Tag
{
    public function __construct(
        public readonly string $id,
        public readonly string $label,
        public readonly string $slug,
        public readonly ?string $url = null,
    ) {
    }

    /** A URL-safe slug for a label: lower case ASCII letters, digits and dashes. */
    public static function slugify(string $label): string
    {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $label);
        $ascii = $ascii === false ? $label : $ascii;
        $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($ascii)), '-');

        return $slug === '' ? substr(md5($label), 0, 12) : $slug;
    }
}
