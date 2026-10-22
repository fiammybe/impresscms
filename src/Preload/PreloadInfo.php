<?php

declare(strict_types=1);

namespace App\Preload;

final class PreloadInfo
{
    /**
     * @param list<string> $events event names as written in the class, such as "beforeFooter"
     */
    public function __construct(
        public readonly string $name,
        public readonly string $className,
        public readonly bool $enabled,
        public readonly array $events,
    ) {
    }
}
