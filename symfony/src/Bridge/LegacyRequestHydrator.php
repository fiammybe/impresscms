<?php

declare(strict_types=1);

namespace App\Bridge;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

final class LegacyRequestHydrator
{
    /** @return array{server:array,get:array,post:array,cookie:array,files:array,request:array,cwd:string|false,status:int|false} */
    public function snapshot(): array
    {
        return [
            'server' => $_SERVER,
            'get' => $_GET,
            'post' => $_POST,
            'cookie' => $_COOKIE,
            'files' => $_FILES,
            'request' => $_REQUEST,
            'cwd' => getcwd(),
            'status' => http_response_code(),
        ];
    }

    /** @param array{script_path:string,script_name:string,resolution:string} $resolution */
    public function hydrate(Request $request, array $resolution, string $legacyRoot): void
    {
        $_GET = $request->query->all();
        $_POST = $request->request->all();
        $_COOKIE = $request->cookies->all();
        $_FILES = $this->convertFiles($request->files->all());
        $_REQUEST = array_merge($_GET, $_POST, $_COOKIE);

        $_SERVER = array_merge($_SERVER, $request->server->all());
        $_SERVER['REQUEST_METHOD'] = $request->getMethod();
        $_SERVER['REQUEST_URI'] = $request->getRequestUri();
        $_SERVER['QUERY_STRING'] = (string) $request->server->get('QUERY_STRING', '');
        $_SERVER['SCRIPT_NAME'] = $resolution['script_name'];
        $_SERVER['PHP_SELF'] = $resolution['script_name'];
        $_SERVER['SCRIPT_FILENAME'] = $resolution['script_path'];
        $_SERVER['DOCUMENT_ROOT'] = $legacyRoot;
        $_SERVER['SERVER_PORT'] = (string) $request->getPort();
        $_SERVER['HTTP_HOST'] = (string) $request->getHost();
        $_SERVER['SERVER_NAME'] = (string) $request->getHost();
        $_SERVER['HTTPS'] = $request->isSecure() ? 'on' : 'off';
    }

    /** @param array{server:array,get:array,post:array,cookie:array,files:array,request:array,cwd:string|false,status:int|false} $snapshot */
    public function restore(array $snapshot): void
    {
        $_SERVER = $snapshot['server'];
        $_GET = $snapshot['get'];
        $_POST = $snapshot['post'];
        $_COOKIE = $snapshot['cookie'];
        $_FILES = $snapshot['files'];
        $_REQUEST = $snapshot['request'];

        if ($snapshot['cwd'] !== false) {
            chdir($snapshot['cwd']);
        }

        if ($snapshot['status'] !== false) {
            http_response_code($snapshot['status']);
        }
    }

    /** @param array<string,mixed> $files */
    private function convertFiles(array $files): array
    {
        $result = [];
        foreach ($files as $key => $value) {
            $result[$key] = $this->normalizeFileValue($value);
        }

        return $result;
    }

    private function normalizeFileValue(mixed $value): mixed
    {
        if ($value instanceof UploadedFile) {
            return [
                'name' => $value->getClientOriginalName(),
                'type' => $value->getClientMimeType(),
                'tmp_name' => $value->getPathname(),
                'error' => $value->getError(),
                'size' => $value->getSize() ?? 0,
            ];
        }

        if (is_array($value)) {
            $normalized = [];
            foreach ($value as $k => $nestedValue) {
                $normalized[$k] = $this->normalizeFileValue($nestedValue);
            }

            return $normalized;
        }

        return $value;
    }
}
