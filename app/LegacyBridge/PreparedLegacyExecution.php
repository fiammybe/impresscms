<?php

declare(strict_types=1);

namespace App\LegacyBridge;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PreparedLegacyExecution
{
    public bool $completed = false;
    public bool $responseSent = false;
    public ?Response $response = null;

    /**
     * @param array<string, mixed> $snapshot
     */
    public function __construct(
        public readonly Request $request,
        public readonly LegacyResolution $resolution,
        public readonly array $snapshot,
        public readonly int $outputBufferLevel,
        public readonly bool $debug,
    ) {
    }
}
