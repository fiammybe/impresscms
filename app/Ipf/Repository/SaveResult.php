<?php

declare(strict_types=1);

namespace App\Ipf\Repository;

/**
 * The outcome of storing or deleting an item: its id, whether it was created,
 * and the handler's or entity's errors (empty on success).
 */
final class SaveResult
{
    /**
     * @param list<string> $errors
     */
    public function __construct(
        public readonly ?int $id,
        public readonly bool $created,
        public readonly array $errors = [],
        public readonly bool $found = true,
    ) {
    }

    public function succeeded(): bool
    {
        return $this->found && $this->errors === [];
    }
}
