<?php

declare(strict_types=1);

namespace App\Ipf\Eloquent\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * XOBJ_DTYPE_SIMPLE_ARRAY: a list stored as its values joined with `|`
 * (Icms\Ipf\Entity::cleanVars() and getVar()).
 *
 * @implements CastsAttributes<list<string>, list<string>|string>
 */
final class PipeList implements CastsAttributes
{
    /**
     * @param array<string, mixed> $attributes
     * @return list<string>
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        return $value === null || $value === '' ? [] : explode('|', (string) $value);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return is_array($value) ? implode('|', array_map('strval', $value)) : (string) $value;
    }
}
