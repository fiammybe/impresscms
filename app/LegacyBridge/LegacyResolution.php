<?php

declare(strict_types=1);

namespace App\LegacyBridge;

final class LegacyResolution
{
    public function __construct(
        public readonly string $strategy,
        public readonly string $requestPath,
        public readonly string $resolvedPath,
        public readonly string $workingDirectory,
        public readonly string $scriptName,
    ) {
    }

    public function isDirectAsset(): bool
    {
        return $this->strategy === 'direct_asset';
    }

    public function isDirectScript(): bool
    {
        return $this->strategy === 'direct_script';
    }
}
