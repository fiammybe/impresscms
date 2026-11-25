<?php

declare(strict_types=1);

namespace App\Ipf\Definition;

/**
 * A notification the handler sends (Icms\Ipf\Metadata\Notification).
 */
final class NotificationDefinition
{
    public function __construct(
        public readonly string $category,
        public readonly string $event,
        public readonly string $after,
    ) {
    }
}
