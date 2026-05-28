<?php

declare(strict_types=1);

namespace App\Bridge;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class LegacyBridge
{
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
        $snapshot = $this->requestHydrator->snapshot();
        $initialOutputBufferLevel = ob_get_level();

        if (headers_sent()) {
            return new Response('Cannot bridge legacy request because headers were already sent.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        try {
            header_remove();
            http_response_code(200);

            $this->requestHydrator->hydrate($request, $resolution, $this->legacyRoot);
            chdir($this->legacyRoot);

            if ($debug) {
                error_log(json_encode([
                    'component' => 'migration_bridge',
                    'resolved_script' => $resolution['script_path'],
                    'resolution' => $resolution['resolution'],
                ], JSON_UNESCAPED_SLASHES));
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
            error_log(json_encode([
                'component' => 'migration_bridge',
                'error' => $exception->getMessage(),
                'type' => $exception::class,
                'path' => $request->getPathInfo(),
            ], JSON_UNESCAPED_SLASHES));

            if ($debug) {
                return new Response(
                    "Legacy bridge failure: {$exception->getMessage()}\n{$exception->getTraceAsString()}",
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                    ['content-type' => 'text/plain; charset=UTF-8']
                );
            }

            return new Response('A legacy bridge error occurred.', Response::HTTP_INTERNAL_SERVER_ERROR);
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
}
