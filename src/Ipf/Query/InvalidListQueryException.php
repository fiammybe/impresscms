<?php

declare(strict_types=1);

namespace App\Ipf\Query;

/**
 * A list was asked to sort, filter or search on something the object's
 * description does not allow.
 */
final class InvalidListQueryException extends \InvalidArgumentException
{
    public static function unknownField(string $object, string $purpose, string $field): self
    {
        return new self(sprintf('The object "%s" cannot %s by "%s".', $object, $purpose, $field));
    }
}
