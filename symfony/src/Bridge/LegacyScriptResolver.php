<?php

declare(strict_types=1);

namespace App\Bridge;

use Symfony\Component\HttpFoundation\Request;

final class LegacyScriptResolver
{
    /** @var string[] */
    private array $blockedPathSegments = ['cache', 'templates_c', 'vendor', '.git'];

    public function __construct(
        private readonly string $legacyRoot,
        private readonly string $legacyFrontController
    ) {
    }

    /** @return array{script_path:string,script_name:string,resolution:string} */
    public function resolve(Request $request): array
    {
        $path = rawurldecode($request->getPathInfo());

        if ($path === '/' || $path === '' || $path === '/index.php') {
            return [
                'script_path' => $this->legacyFrontController,
                'script_name' => '/index.php',
                'resolution' => 'legacy_front_controller',
            ];
        }

        if (str_contains($path, "\0") || str_contains($path, '..')) {
            return [
                'script_path' => $this->legacyFrontController,
                'script_name' => '/index.php',
                'resolution' => 'unsafe_path_fallback',
            ];
        }

        $candidatePaths = [$path];
        if (!str_ends_with($path, '.php')) {
            $candidatePaths[] = rtrim($path, '/') . '/index.php';
        }

        foreach ($candidatePaths as $candidatePath) {
            $resolved = $this->resolveCandidate($candidatePath);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        return [
            'script_path' => $this->legacyFrontController,
            'script_name' => '/index.php',
            'resolution' => 'fallback_front_controller',
        ];
    }

    /** @return array{script_path:string,script_name:string,resolution:string}|null */
    private function resolveCandidate(string $candidatePath): ?array
    {
        $cleanPath = '/' . ltrim($candidatePath, '/');
        $segments = array_filter(explode('/', trim($cleanPath, '/')));

        foreach ($segments as $segment) {
            if (in_array($segment, $this->blockedPathSegments, true)) {
                return null;
            }
        }

        $fullPath = $this->legacyRoot . $cleanPath;
        if (!is_file($fullPath)) {
            return null;
        }

        $realPath = realpath($fullPath);
        $realLegacyRoot = realpath($this->legacyRoot);
        if ($realPath === false || $realLegacyRoot === false || !str_starts_with($realPath, $realLegacyRoot . DIRECTORY_SEPARATOR)) {
            return null;
        }

        return [
            'script_path' => $realPath,
            'script_name' => $cleanPath,
            'resolution' => str_ends_with($cleanPath, '.php') ? 'direct_script' : 'direct_asset',
        ];
    }
}
