<?php

declare(strict_types=1);

namespace App\LegacyBridge;

use ErrorException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class LegacyBridge
{
    /**
     * @param array<string, string> $mimeTypes
     */
    public function __construct(
        private readonly LegacyScriptResolver $resolver,
        private readonly LegacyRequestHydrator $hydrator,
        private readonly string $legacyRoot,
        private readonly array $mimeTypes,
        private readonly bool $debug,
    ) {
    }

    public function resolve(Request $request): LegacyResolution
    {
        return $this->resolver->resolve($request);
    }

    public function createAssetResponse(LegacyResolution $resolution): BinaryFileResponse
    {
        $response = new BinaryFileResponse($resolution->resolvedPath);
        $response->headers->set('Content-Type', $this->resolveMimeType($resolution->resolvedPath));
        $response->headers->set('X-Legacy-Bridge-Strategy', $resolution->strategy);
        $response->setAutoLastModified();
        $response->setAutoEtag();
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, basename($resolution->resolvedPath));

        return $response;
    }

    public function prepare(Request $request, LegacyResolution $resolution): PreparedLegacyExecution
    {
        if (!headers_sent()) {
            header_remove();
        }

        $snapshot = $this->hydrator->hydrate($request, $resolution, $this->legacyRoot);
        $outputBufferLevel = ob_get_level();
        ob_start();

        $execution = new PreparedLegacyExecution(
            request: $request,
            resolution: $resolution,
            snapshot: $snapshot,
            outputBufferLevel: $outputBufferLevel,
            debug: $this->debug,
        );

        register_shutdown_function(function () use ($execution): void {
            if ($execution->completed || $execution->responseSent) {
                return;
            }

            $response = $this->finalize($execution, $this->exceptionFromLastError());
            $execution->responseSent = true;
            $response->send();
        });

        return $execution;
    }

    public function complete(PreparedLegacyExecution $execution): Response
    {
        return $this->finalize($execution);
    }

    public function fail(PreparedLegacyExecution $execution, Throwable $exception): Response
    {
        return $this->finalize($execution, $exception);
    }

    public function renderBridgeFailure(Request $request, Throwable $exception, ?LegacyResolution $resolution = null): Response
    {
        return $this->renderExceptionResponse($request, $resolution, $exception);
    }

    private function finalize(PreparedLegacyExecution $execution, ?Throwable $exception = null): Response
    {
        if ($execution->completed && $execution->response instanceof Response) {
            return $execution->response;
        }

        $content = $this->drainOutputBuffers($execution->outputBufferLevel);
        $response = $exception instanceof Throwable
            ? $this->renderExceptionResponse($execution->request, $execution->resolution, $exception, $content)
            : $this->buildLegacyResponse($execution->resolution, $content);

        if (!headers_sent()) {
            header_remove();
        }

        $this->hydrator->restore($execution->snapshot);

        $execution->completed = true;
        $execution->response = $response;

        return $response;
    }

    private function buildLegacyResponse(LegacyResolution $resolution, string $content): Response
    {
        $statusCode = http_response_code();
        $statusCode = is_int($statusCode) && $statusCode >= 100 ? $statusCode : 200;

        $response = new Response($content, $statusCode);
        $response->headers->set('X-Legacy-Bridge-Strategy', $resolution->strategy);
        $response->headers->set('X-Legacy-Bridge-Script', $resolution->scriptName);

        foreach (headers_list() as $headerLine) {
            $parts = explode(':', $headerLine, 2);
            $name = trim($parts[0]);
            $value = isset($parts[1]) ? trim($parts[1]) : '';

            if ($name === '') {
                continue;
            }

            if (strcasecmp($name, 'Set-Cookie') === 0 && method_exists(Cookie::class, 'fromString')) {
                $response->headers->setCookie(Cookie::fromString($value));
                continue;
            }

            $response->headers->set($name, $value, false);
        }

        return $response;
    }

    private function renderExceptionResponse(
        Request $request,
        ?LegacyResolution $resolution,
        Throwable $exception,
        string $capturedOutput = '',
    ): Response {
        $lines = [
            'Legacy bridge failure',
            '',
            'Request path: ' . $request->getPathInfo(),
            'Request URI: ' . $request->getRequestUri(),
            'Method: ' . strtoupper($request->getMethod()),
            'Resolution strategy: ' . ($resolution?->strategy ?? 'unresolved'),
            'Resolved legacy script: ' . ($resolution?->resolvedPath ?? 'n/a'),
            'Exception: ' . $exception::class . ': ' . $exception->getMessage(),
            'Exception location: ' . $exception->getFile() . ':' . $exception->getLine(),
        ];

        if ($capturedOutput !== '') {
            $lines[] = 'Captured output:';
            $lines[] = rtrim($capturedOutput);
        }

        if ($this->debug) {
            $lines[] = 'Stack trace:';
            $lines[] = $exception->getTraceAsString();
        }

        $statusCode = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : Response::HTTP_INTERNAL_SERVER_ERROR;

        $headers = [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'X-Legacy-Bridge-Strategy' => $resolution?->strategy ?? 'unresolved',
        ];

        if ($exception instanceof HttpExceptionInterface) {
            $headers = array_merge($headers, $exception->getHeaders());
        }

        $response = new Response(implode("\n", $lines) . "\n", $statusCode, $headers);

        return $response;
    }

    private function drainOutputBuffers(int $startingLevel): string
    {
        $content = '';

        while (ob_get_level() > $startingLevel) {
            $buffer = ob_get_clean();
            if ($buffer !== false) {
                $content = $buffer . $content;
            }
        }

        return $content;
    }

    private function resolveMimeType(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (isset($this->mimeTypes[$extension])) {
            return $this->mimeTypes[$extension];
        }

        if (function_exists('finfo_open')) {
            $handle = finfo_open(FILEINFO_MIME_TYPE);
            if ($handle !== false) {
                $mimeType = finfo_file($handle, $path) ?: null;
                finfo_close($handle);
                if (is_string($mimeType) && $mimeType !== '') {
                    return $mimeType;
                }
            }
        }

        $mimeType = @mime_content_type($path);

        return is_string($mimeType) && $mimeType !== '' ? $mimeType : 'application/octet-stream';
    }

    private function exceptionFromLastError(): ?Throwable
    {
        $lastError = error_get_last();
        if ($lastError === null || !in_array($lastError['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR], true)) {
            return null;
        }

        return new ErrorException(
            $lastError['message'],
            0,
            $lastError['type'],
            $lastError['file'],
            $lastError['line'],
        );
    }
}
