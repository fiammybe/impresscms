<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

final class MigrationStatusController
{
    public function __construct(private readonly string $legacyRoot)
    {
    }

    public function __invoke(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'ok',
            'symfony_booted' => true,
            'legacy_bridge_available' => is_dir($this->legacyRoot) && is_file($this->legacyRoot . '/legacy_index.php'),
        ]);
    }
}
