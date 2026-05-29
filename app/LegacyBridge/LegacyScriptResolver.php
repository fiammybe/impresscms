<?php

declare(strict_types=1);

namespace App\LegacyBridge;

use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class LegacyScriptResolver
{
    /**
     * @param list<string> $assetExtensions
     */
    public function __construct(
        private readonly string $legacyRoot,
        private readonly string $frontController,
        private readonly array $assetExtensions,
    ) {
    }

    public function resolve(Request $request): LegacyResolution
    {
        $requestPath = '/' . ltrim(rawurldecode($request->getPathInfo()), '/');
        $requestPath = $requestPath === '//' ? '/' : $requestPath;

        if ($this->containsTraversal($requestPath)) {
            throw new RuntimeException(sprintf('Unsafe legacy path [%s].', $requestPath));
        }

        $relativePath = ltrim($requestPath, '/');
        $candidatePath = $relativePath === '' ? $this->legacyRoot : $this->legacyRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

        if (is_dir($candidatePath)) {
            foreach (['index.php', 'index.html'] as $directoryIndex) {
                $indexedPath = $candidatePath . DIRECTORY_SEPARATOR . $directoryIndex;
                if (is_file($indexedPath)) {
                    return $this->buildResolution($requestPath, $indexedPath, $this->isPhpFile($indexedPath) ? 'direct_script' : 'direct_asset');
                }
            }
        }

        if (is_file($candidatePath)) {
            return $this->buildResolution($requestPath, $candidatePath, $this->isPhpFile($candidatePath) ? 'direct_script' : 'direct_asset');
        }

        $extension = strtolower(pathinfo($candidatePath, PATHINFO_EXTENSION));
        if ($extension !== '' && $extension !== 'php' && in_array($extension, $this->assetExtensions, true)) {
            throw new NotFoundHttpException(sprintf('Legacy asset [%s] was not found.', $requestPath));
        }

        return $this->buildResolution($requestPath, $this->legacyRoot . DIRECTORY_SEPARATOR . $this->frontController, 'fallback_front_controller');
    }

    private function buildResolution(string $requestPath, string $candidatePath, string $strategy): LegacyResolution
    {
        $realPath = realpath($candidatePath);
        if ($realPath === false) {
            throw new NotFoundHttpException(sprintf('Legacy target [%s] could not be resolved.', $candidatePath));
        }

        $legacyRoot = rtrim(str_replace('\\', '/', $this->legacyRoot), '/');
        $normalizedRealPath = str_replace('\\', '/', $realPath);

        if ($normalizedRealPath !== $legacyRoot && !str_starts_with($normalizedRealPath, $legacyRoot . '/')) {
            throw new RuntimeException(sprintf('Resolved legacy target [%s] escapes [%s].', $normalizedRealPath, $legacyRoot));
        }

        $relativeResolvedPath = ltrim(substr($normalizedRealPath, strlen($legacyRoot)), '/');
        $scriptName = '/' . str_replace('\\', '/', $relativeResolvedPath);

        return new LegacyResolution(
            strategy: $strategy,
            requestPath: $requestPath,
            resolvedPath: $normalizedRealPath,
            workingDirectory: str_replace('\\', '/', dirname($normalizedRealPath)),
            scriptName: $scriptName,
        );
    }

    private function containsTraversal(string $requestPath): bool
    {
        foreach (explode('/', str_replace('\\', '/', $requestPath)) as $segment) {
            if ($segment === '..') {
                return true;
            }
        }

        return false;
    }

    private function isPhpFile(string $path): bool
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'php';
    }
}
