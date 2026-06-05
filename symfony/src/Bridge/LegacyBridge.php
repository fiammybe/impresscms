<?php

declare(strict_types=1);

namespace App\Bridge;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\MimeTypes;

final class LegacyBridge
{
    /** @var string[] */
    private const ALWAYS_REFERENCED_GLOBALS = [
        'icmsTheme',
        'icmsTpl',
        'xoopsOption',
        'xoopsTpl',
        'xoTheme',
    ];

    public function __construct(
        private readonly LegacyScriptResolver $scriptResolver,
        private readonly LegacyRequestHydrator $requestHydrator,
        private readonly string $legacyRoot
    ) {
    }

    public function isAvailable(): bool
    {
        return is_dir($this->legacyRoot) && is_file($this->legacyRoot . '/legacy_index.php');
    }

    public function handle(Request $request, bool $debug): Response
    {
        if (!$this->isAvailable()) {
            return new Response('Legacy runtime is unavailable.', Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $resolution = $this->scriptResolver->resolve($request);
        $bridgeContext = $this->buildBridgeContext($request, $resolution);
        $snapshot = $this->requestHydrator->snapshot();
        $initialOutputBufferLevel = ob_get_level();

        if (headers_sent()) {
            return new Response('Cannot bridge legacy request because headers were already sent.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        try {
            if ($resolution['resolution'] === 'direct_asset') {
                return $this->serveAsset($resolution['script_path']);
            }

            $legacyGlobalReferences = $this->createLegacyGlobalReferences($resolution['script_path']);
            if ($legacyGlobalReferences !== []) {
                extract($legacyGlobalReferences, EXTR_REFS);
            }

            header_remove();
            http_response_code(200);

            $this->requestHydrator->hydrate($request, $resolution, $this->legacyRoot);
            chdir(\dirname($resolution['script_path']));

            if ($debug) {
                $this->logBridgeEvent($bridgeContext);
            }

            ob_start();
            include $resolution['script_path'];
            $content = ob_get_clean();

            $statusCode = http_response_code();
            $statusCode = is_int($statusCode) && $statusCode > 0 ? $statusCode : 200;

            $response = new Response($content === false ? '' : $content, $statusCode);
            foreach (headers_list() as $headerLine) {
                $this->applyHeader($response, $headerLine);
            }

            header_remove();

            return $response;
        } catch (\Throwable $exception) {
            return $this->createErrorResponse($exception, $bridgeContext, $debug);
        } finally {
            while (ob_get_level() > $initialOutputBufferLevel) {
                ob_end_clean();
            }

            header_remove();
            $this->requestHydrator->restore($snapshot);
        }
    }

    private function applyHeader(Response $response, string $headerLine): void
    {
        if (stripos($headerLine, 'HTTP/') === 0) {
            return;
        }

        $parts = explode(':', $headerLine, 2);
        if (count($parts) !== 2) {
            return;
        }

        $name = trim($parts[0]);
        $value = trim($parts[1]);

        if ($name === '' || $value === '') {
            return;
        }

        if (strcasecmp($name, 'Set-Cookie') === 0) {
            if (method_exists(Cookie::class, 'fromString')) {
                $response->headers->setCookie(Cookie::fromString($value));
            } else {
                $response->headers->set('Set-Cookie', $value, false);
            }
            return;
        }

        $response->headers->set($name, $value, false);
    }

    private function serveAsset(string $assetPath): Response
    {
        $response = new BinaryFileResponse($assetPath);

        $mimeType = $this->detectMimeType($assetPath);
        if ($mimeType !== null) {
            $response->headers->set('Content-Type', $mimeType);
        }

        return $response;
    }

    private function detectMimeType(string $assetPath): ?string
    {
        $extensionMimeType = $this->detectMimeTypeFromExtension($assetPath);
        if ($extensionMimeType !== null) {
            return $extensionMimeType;
        }

        if (is_file($assetPath) && function_exists('mime_content_type')) {
            $detectedMimeType = mime_content_type($assetPath);
            if (is_string($detectedMimeType) && $detectedMimeType !== '') {
                return $detectedMimeType;
            }
        }

        return null;
    }

    private function detectMimeTypeFromExtension(string $assetPath): ?string
    {
        $extension = strtolower(pathinfo($assetPath, PATHINFO_EXTENSION));
        if ($extension === '') {
            return null;
        }

        if (class_exists(MimeTypes::class)) {
            $mimeTypes = MimeTypes::getDefault()->getMimeTypes($extension);
            if ($mimeTypes !== []) {
                return $mimeTypes[0];
            }
        }

        return match ($extension) {
            'css' => 'text/css',
            'js', 'mjs' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'svg' => 'image/svg+xml',
            'html', 'htm' => 'text/html',
            'csv' => 'text/csv',
            default => null,
        };
    }

    /** @return array<string, mixed> */
    private function createLegacyGlobalReferences(string $scriptPath): array
    {
        $references = [];

        foreach (self::ALWAYS_REFERENCED_GLOBALS as $variableName) {
            if (!array_key_exists($variableName, $GLOBALS)) {
                $GLOBALS[$variableName] = null;
            }

            $references[$variableName] = &$GLOBALS[$variableName];
        }

        $scriptContents = @file_get_contents($scriptPath);
        if (!is_string($scriptContents) || $scriptContents === '') {
            return $references;
        }

        if (!preg_match_all('/\bglobal\s+([^;]+);/', $scriptContents, $globalStatements)) {
            return $references;
        }

        foreach ($globalStatements[1] as $statement) {
            if (!preg_match_all('/\$([A-Za-z_][A-Za-z0-9_]*)/', $statement, $variableMatches)) {
                continue;
            }

            foreach ($variableMatches[1] as $variableName) {
                if (!array_key_exists($variableName, $GLOBALS)) {
                    $GLOBALS[$variableName] = null;
                }

                $references[$variableName] = &$GLOBALS[$variableName];
            }
        }

        return $references;
    }

    /** @param array{script_path:string,script_name:string,resolution:string} $resolution */
    private function buildBridgeContext(Request $request, array $resolution): array
    {
        return [
            'component' => 'migration_bridge',
            'path' => $request->getPathInfo(),
            'request_uri' => $request->getRequestUri(),
            'method' => $request->getMethod(),
            'resolution' => $resolution['resolution'],
            'resolved_script' => $resolution['script_path'],
            'script_name' => $resolution['script_name'],
            'legacy_root' => $this->legacyRoot,
        ];
    }

    /** @param array<string, scalar|null> $context */
    private function logBridgeEvent(array $context): void
    {
        error_log(json_encode($context, JSON_UNESCAPED_SLASHES));
    }

    /** @param array<string, scalar|null> $bridgeContext */
    private function createErrorResponse(\Throwable $exception, array $bridgeContext, bool $debug): Response
    {
        $errorContext = array_merge($bridgeContext, [
            'error' => $exception->getMessage(),
            'type' => $exception::class,
            'exception_file' => $exception->getFile(),
            'exception_line' => $exception->getLine(),
        ]);

        $this->logBridgeEvent($errorContext);

        $message = [
            'Legacy bridge failure.',
            'Request path: ' . ($bridgeContext['path'] ?? ''),
            'Request URI: ' . ($bridgeContext['request_uri'] ?? ''),
            'Method: ' . ($bridgeContext['method'] ?? ''),
            'Resolution: ' . ($bridgeContext['resolution'] ?? ''),
            'Resolved script: ' . ($bridgeContext['resolved_script'] ?? ''),
            'Exception: ' . $exception::class,
            'Message: ' . $exception->getMessage(),
            'Location: ' . $exception->getFile() . ':' . $exception->getLine(),
        ];

        if ($debug) {
            $message[] = '';
            $message[] = 'Stack trace:';
            $message[] = $exception->getTraceAsString();
        }

        return new Response(
            implode("\n", $message),
            Response::HTTP_INTERNAL_SERVER_ERROR,
            ['content-type' => 'text/plain; charset=UTF-8']
        );
    }
}
