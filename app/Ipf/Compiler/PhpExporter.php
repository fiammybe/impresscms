<?php

declare(strict_types=1);

namespace App\Ipf\Compiler;

use InvalidArgumentException;

/**
 * Deterministic PHP literals for generated code: short arrays, one entry
 * per line, lists without keys, single-quoted strings. The same value always
 * gives the same bytes.
 */
final class PhpExporter
{
    public static function export(mixed $value, int $indent = 0): string
    {
        return match (true) {
            $value === null => 'null',
            is_bool($value) => $value ? 'true' : 'false',
            is_int($value) => (string) $value,
            is_float($value) => self::float($value),
            is_string($value) => var_export($value, true),
            is_array($value) => self::array($value, $indent),
            default => throw new InvalidArgumentException('Generated code can only hold scalars, null and arrays, not ' . get_debug_type($value) . '.'),
        };
    }

    /**
     * @param array<mixed> $value
     */
    private static function array(array $value, int $indent): string
    {
        if ($value === []) {
            return '[]';
        }

        $pad = str_repeat('    ', $indent + 1);
        $list = array_is_list($value);
        $lines = [];
        foreach ($value as $key => $item) {
            $prefix = $list ? '' : self::export($key) . ' => ';
            $lines[] = $pad . $prefix . self::export($item, $indent + 1) . ',';
        }

        return "[\n" . implode("\n", $lines) . "\n" . str_repeat('    ', $indent) . ']';
    }

    private static function float(float $value): string
    {
        $text = var_export($value, true);

        return str_contains($text, '.') || str_contains($text, 'E') ? $text : "{$text}.0";
    }
}
