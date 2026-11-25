<?php

declare(strict_types=1);

namespace App\Ipf\Definition;

use RuntimeException;

/**
 * IPF metadata the definition model cannot represent. Raised instead of
 * dropping the metadata, naming where it was declared.
 */
final class UnsupportedDefinitionException extends RuntimeException
{
    public static function at(string $where, string $problem): self
    {
        return new self("{$problem} in {$where}. Support it in App\\Ipf\\Definition\\DefinitionReader, or remove it from the declaration.");
    }
}
