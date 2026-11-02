<?php

declare(strict_types=1);

use App\Kernel;
use Symfony\Component\HttpFoundation\Request;

$projectDir = dirname(__DIR__);
$autoloadFile = $projectDir . '/vendor/autoload.php';

if (!is_file($autoloadFile)) {
    require dirname($projectDir) . '/htdocs/legacy_index.php';
    return;
}

require $autoloadFile;

if (is_file($projectDir . '/.env')) {
    foreach (['.env', '.env.local'] as $envFile) {
        $fullPath = $projectDir . '/' . $envFile;
        if (!is_file($fullPath)) {
            continue;
        }

        $lines = file($fullPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
                continue;
            }

            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value, " \t\n\r\0\x0B\"");
            if ($name !== '' && getenv($name) === false) {
                putenv($name . '=' . $value);
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

$env = $_SERVER['APP_ENV'] ?? 'prod';
$debug = filter_var($_SERVER['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL);

$kernel = new Kernel($env, $debug);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
