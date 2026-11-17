<?php

declare(strict_types=1);

namespace App\Ipf\Query;

/**
 * What a list asks for (design D3): the same for every store and renderer.
 */
final class ListQuery
{
    public const ASC = 'ASC';
    public const DESC = 'DESC';

    /**
     * @param list<string>              $searchFields the fields the search text is looked for in
     * @param array<string, int|string> $filters      field => exact value
     * @param int                       $limit        0 for no limit
     */
    public function __construct(
        public readonly ?string $sort = null,
        public readonly string $order = self::ASC,
        public readonly int $offset = 0,
        public readonly int $limit = 15,
        public readonly string $search = '',
        public readonly array $searchFields = [],
        public readonly array $filters = [],
    ) {
    }

    public function hasSearch(): bool
    {
        return $this->search !== '' && $this->searchFields !== [];
    }

    /**
     * The page this query shows, counted from 1.
     */
    public function page(): int
    {
        if ($this->limit < 1) {
            return 1;
        }

        return intdiv($this->offset, $this->limit) + 1;
    }
}
