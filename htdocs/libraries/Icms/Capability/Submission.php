<?php

/**
 * The values one capability received from a submitted form
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		ICMS\Capability
 */

declare(strict_types=1);

namespace Icms\Capability;

/**
 * The fields a form contributor added under its prefix
 * (capability[<name>][<field>]), without the prefix.
 */
final class Submission
{
    /** @param array<string, mixed> $values */
    public function __construct(
        public readonly string $capability,
        private readonly array $values,
    ) {
    }

    public function has(string $field): bool
    {
        return array_key_exists($field, $this->values);
    }

    public function get(string $field, mixed $default = null): mixed
    {
        return $this->values[$field] ?? $default;
    }

    public function string(string $field): string
    {
        $value = $this->get($field, '');

        return is_scalar($value) ? trim((string) $value) : '';
    }

    /** @return list<string> */
    public function strings(string $field): array
    {
        $value = $this->get($field, []);
        $value = is_array($value) ? $value : [$value];

        return array_values(array_filter(array_map(
            static fn (mixed $item): string => is_scalar($item) ? trim((string) $item) : '',
            $value,
        ), static fn (string $item): bool => $item !== ''));
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return $this->values;
    }
}
