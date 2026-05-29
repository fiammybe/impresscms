<?php

declare(strict_types=1);

namespace App\LegacyBridge;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class LegacyRequestHydrator
{
    /**
     * @return array<string, mixed>
     */
    public function hydrate(Request $request, LegacyResolution $resolution, string $legacyRoot): array
    {
        $snapshot = [
            '_SERVER' => $_SERVER,
            '_GET' => $_GET,
            '_POST' => $_POST,
            '_COOKIE' => $_COOKIE,
            '_FILES' => $_FILES,
            '_REQUEST' => $_REQUEST,
            '_SESSION' => isset($_SESSION) && is_array($_SESSION) ? $_SESSION : null,
            'session_status' => session_status(),
            'session_id' => session_id(),
            'headers' => headers_list(),
            'response_code' => http_response_code(),
            'cwd' => getcwd() ?: null,
        ];

        $_GET = $request->query->all();
        $_POST = $request->request->all();
        $_COOKIE = $request->cookies->all();
        $_FILES = $this->normalizeUploadedFiles($request->files->all());
        $_REQUEST = array_replace($_COOKIE, $_GET, $_POST);

        $_SERVER = array_replace($_SERVER, $request->server->all());
        $_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', $legacyRoot);
        $_SERVER['REQUEST_METHOD'] = strtoupper($request->getMethod());
        $_SERVER['REQUEST_URI'] = $request->getRequestUri();
        $_SERVER['QUERY_STRING'] = $request->getQueryString() ?? '';
        $_SERVER['SCRIPT_FILENAME'] = $resolution->resolvedPath;
        $_SERVER['SCRIPT_NAME'] = $resolution->scriptName;
        $_SERVER['PHP_SELF'] = $resolution->scriptName;
        $_SERVER['PATH_TRANSLATED'] = $resolution->resolvedPath;
        $_SERVER['REQUEST_TIME'] = time();
        $_SERVER['REQUEST_TIME_FLOAT'] = microtime(true);

        if (!isset($_SERVER['HTTP_HOST']) && $request->getHost() !== '') {
            $_SERVER['HTTP_HOST'] = $request->getHost();
        }

        $this->hydrateSession($request, $snapshot);

        return $snapshot;
    }

    /**
     * @param array<string, mixed> $snapshot
     */
    public function restore(array $snapshot): void
    {
        if (session_status() === PHP_SESSION_ACTIVE && $snapshot['session_status'] !== PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $_SERVER = $snapshot['_SERVER'];
        $_GET = $snapshot['_GET'];
        $_POST = $snapshot['_POST'];
        $_COOKIE = $snapshot['_COOKIE'];
        $_FILES = $snapshot['_FILES'];
        $_REQUEST = $snapshot['_REQUEST'];

        if ($snapshot['_SESSION'] !== null || isset($_SESSION)) {
            $_SESSION = $snapshot['_SESSION'] ?? [];
        }

        if ($snapshot['cwd'] !== null && is_string($snapshot['cwd'])) {
            chdir($snapshot['cwd']);
        }

        if (!headers_sent()) {
            header_remove();
            foreach ($snapshot['headers'] as $header) {
                header($header, false);
            }
            if (is_int($snapshot['response_code']) && $snapshot['response_code'] >= 100) {
                http_response_code($snapshot['response_code']);
            }
        }
    }

    /**
     * @param array<string, mixed> $snapshot
     */
    private function hydrateSession(Request $request, array $snapshot): void
    {
        $incomingSessionId = $request->cookies->get(session_name());
        if ($incomingSessionId !== null && session_status() !== PHP_SESSION_ACTIVE) {
            session_id((string) $incomingSessionId);
        }

        if (session_status() !== PHP_SESSION_ACTIVE && ($incomingSessionId !== null || $request->isMethod('POST'))) {
            @session_start();
        }

        if (!isset($_SESSION) || !is_array($_SESSION)) {
            $_SESSION = $snapshot['_SESSION'] ?? [];
        }
    }

    /**
     * @param array<string, UploadedFile|array<int|string, mixed>|null> $files
     * @return array<string, mixed>
     */
    private function normalizeUploadedFiles(array $files): array
    {
        $normalized = [];

        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFile) {
                $normalized[$key] = [
                    'name' => $value->getClientOriginalName(),
                    'type' => $value->getClientMimeType(),
                    'tmp_name' => $value->getRealPath() ?: $value->getPathname(),
                    'error' => $value->getError(),
                    'size' => $value->getSize(),
                ];
                continue;
            }

            if (is_array($value)) {
                $normalized[$key] = $this->normalizeUploadedFiles($value);
                continue;
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }
}
