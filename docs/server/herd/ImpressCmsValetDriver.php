<?php

/*
 * Laravel Herd (and Valet) driver for ImpressCMS sites (docs/symfony-host.md).
 *
 * Copy this file into Herd's drivers folder:
 *   Windows: %USERPROFILE%\.config\herd\config\valet\Drivers\
 *   macOS:   ~/Library/Application Support/Herd/config/valet/Drivers/
 *
 * It serves an ImpressCMS web root (mainfile.php and include/host.php), or a
 * repository checkout with the web root in htdocs/. Every PHP request and every
 * unknown path goes to index.php and so to the host (routing level 3, which
 * ImpressCMS requires). Paths that are never served also go to the host, which
 * answers 404. Keep the driver out of the web root: a PHP file there would be
 * reachable from the browser.
 */

namespace Valet\Drivers\Custom;

use Valet\Drivers\ValetDriver;

class ImpressCmsValetDriver extends ValetDriver
{
    /** The host's staging directory, compiled templates, caches, tests, … */
    private const NEVER_SERVED = '#^/(trust_dist|templates_c|cache|tests|plugins/waiting|libraries/tcpdf/tools|install/modules/protector/trust_path)(/|$)|^/uploads/.*\.php$|/\.#i';

    public function serves(string $sitePath, string $siteName, string $uri): bool
    {
        $root = $this->webRoot($sitePath);

        return is_file($root . '/mainfile.php') && is_file($root . '/include/host.php');
    }

    public function beforeLoading(string $sitePath, string $siteName, string $uri): void
    {
        $_SERVER['SERVER_ADDR'] = $_SERVER['SERVER_ADDR'] ?? '127.0.0.1';
        $_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'];
    }

    public function isStaticFile(string $sitePath, string $siteName, string $uri)/*: string|false */
    {
        $file = $this->webRoot($sitePath) . $uri;
        if (preg_match(self::NEVER_SERVED, $uri) || !$this->isActualFile($file) || preg_match('/\.php$/i', $file)) {
            return false;
        }

        return $file;
    }

    public function frontControllerPath(string $sitePath, string $siteName, string $uri): ?string
    {
        $root = $this->webRoot($sitePath);
        $script = $root . '/index.php';

        $_SERVER['SCRIPT_FILENAME'] = $script;
        $_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = '/index.php';
        $_SERVER['DOCUMENT_ROOT'] = $root;

        return $script;
    }

    private function webRoot(string $sitePath): string
    {
        return is_file($sitePath . '/htdocs/include/host.php') ? $sitePath . '/htdocs' : $sitePath;
    }
}
