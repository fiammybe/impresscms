<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$symfonyFrontController = $projectRoot . '/symfony/public/index.php';
$legacyFrontController = __DIR__ . '/legacy_index.php';

if (is_file($symfonyFrontController)) {
    require $symfonyFrontController;
    return;
}

require $legacyFrontController;
