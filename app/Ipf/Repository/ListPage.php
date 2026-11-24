<?php

declare(strict_types=1);

namespace App\Ipf\Repository;

/**
 * One page of an admin list.
 */
final class ListPage
{
    /**
     * @param list<IpfRecord> $records
     */
    public function __construct(
        public readonly array $records,
        public readonly int $total,
        public readonly ListQuery $query,
    ) {
    }
}
