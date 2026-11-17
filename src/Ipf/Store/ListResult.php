<?php

declare(strict_types=1);

namespace App\Ipf\Store;

/**
 * One page of a list, and how many objects match in all.
 */
final class ListResult
{
    /**
     * @param list<object> $items
     */
    public function __construct(public readonly array $items, public readonly int $total)
    {
    }
}
