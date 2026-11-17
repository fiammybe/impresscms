<?php

declare(strict_types=1);

namespace App\Ipf\Metadata;

final class UnknownObjectException extends \RuntimeException
{
    public function __construct(public readonly string $module, public readonly string $item)
    {
        parent::__construct(sprintf('The module "%s" has no object "%s".', $module, $item));
    }
}
