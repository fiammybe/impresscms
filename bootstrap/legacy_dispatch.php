<?php

declare(strict_types=1);

use App\LegacyBridge\LegacyBridge;
use App\LegacyBridge\PreparedLegacyExecution;

$execution = $GLOBALS['__legacy_bridge_execution'] ?? null;
if (!$execution instanceof PreparedLegacyExecution) {
    throw new \RuntimeException('Missing legacy bridge execution context.');
}

$bridge = $GLOBALS['__legacy_bridge'] ?? null;
if (!$bridge instanceof LegacyBridge) {
    throw new \RuntimeException('Missing legacy bridge instance.');
}

try {
    chdir($execution->resolution->workingDirectory);
    require $execution->resolution->resolvedPath;

    return $bridge->complete($execution);
} catch (\Throwable $throwable) {
    return $bridge->fail($execution, $throwable);
} finally {
    unset($GLOBALS['__legacy_bridge_execution'], $GLOBALS['__legacy_bridge']);
}
