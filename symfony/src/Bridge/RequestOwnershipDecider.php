<?php

declare(strict_types=1);

namespace App\Bridge;

use Symfony\Component\HttpFoundation\Request;

final class RequestOwnershipDecider
{
    public const OWNERSHIP_SYMFONY = 'symfony';
    public const OWNERSHIP_LEGACY = 'legacy';

    /** @param string[] $symfonyPrefixes */
    public function __construct(private readonly array $symfonyPrefixes)
    {
    }

    public function decide(Request $request): string
    {
        $path = $request->getPathInfo();

        foreach ($this->symfonyPrefixes as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return self::OWNERSHIP_SYMFONY;
            }
        }

        return self::OWNERSHIP_LEGACY;
    }
}
