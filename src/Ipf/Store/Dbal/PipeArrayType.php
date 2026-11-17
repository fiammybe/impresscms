<?php

declare(strict_types=1);

namespace App\Ipf\Store\Dbal;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * The column type of an IPF XOBJ_DTYPE_SIMPLE_ARRAY variable for ORM-mapped
 * classes: a list stored as one string, joined with "|" (Doctrine's own
 * simple_array joins with commas).
 */
final class PipeArrayType extends Type
{
    public const NAME = 'icms_simple_array';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getClobTypeDeclarationSQL($column);
    }

    /**
     * @return list<string>
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        return explode('|', (string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return implode('|', array_map('strval', (array) $value));
    }
}
