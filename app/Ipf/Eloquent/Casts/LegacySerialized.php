<?php

declare(strict_types=1);

namespace App\Ipf\Eloquent\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * XOBJ_DTYPE_ARRAY: a PHP-serialized array (Icms\Core\Entity::cleanVars()),
 * read without instantiating objects.
 *
 * @implements CastsAttributes<array<mixed>, array<mixed>>
 */
final class LegacySerialized implements CastsAttributes
{
    /**
     * @param array<string, mixed> $attributes
     * @return array<mixed>
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $data = @unserialize((string) $value, ['allowed_classes' => false]);

        return is_array($data) ? $data : [];
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return serialize(is_array($value) ? $value : []);
    }
}
