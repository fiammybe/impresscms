<?php

declare(strict_types=1);

namespace Icms\Routing;

/**
 * Admin URL generator for the ImpressCMS control panel.
 *
 * Generates fully-qualified admin and module URLs from path segments so that
 * admin controllers avoid hard-coded string concatenation.
 *
 * @copyright The ImpressCMS Project <https://www.impresscms.org/>
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package   Icms\Routing
 * @since     2.0
 */
class UrlGenerator
{
    /**
     * Build an absolute URL from a path and optional query parameters.
     *
     * @param string               $path   Relative path (e.g. `'modules/news/admin/index.php'`).
     * @param array<string, mixed> $params Optional query string parameters.
     *
     * @return string Fully-qualified URL.
     */
    public function make(string $path, array $params = []): string
    {
        $base = defined('ICMS_URL') ? rtrim(ICMS_URL, '/') : '';
        $url  = $base . '/' . ltrim($path, '/');

        if (!empty($params)) {
            $url .= '?' . http_build_query($params, '', '&amp;');
        }

        return $url;
    }

    /**
     * Build a URL pointing to the admin control panel.
     *
     * When `$path` is empty the root admin page (`/admin.php`) is returned.
     * Otherwise `$path` is appended after the admin base path.
     *
     * @param string               $path   Optional sub-path within the admin area.
     * @param array<string, mixed> $params Optional query string parameters.
     *
     * @return string Fully-qualified admin URL.
     */
    public function admin(string $path = '', array $params = []): string
    {
        $base     = defined('ICMS_URL') ? rtrim(ICMS_URL, '/') : '';
        $adminBase = $base . '/admin.php';

        if ($path !== '') {
            // Treat $path as an additional GET parameter for the admin router
            // (legacy admin pages use admin.php?fct=<path>).
            $params = array_merge(['fct' => ltrim($path, '/')], $params);
        }

        if (!empty($params)) {
            $adminBase .= '?' . http_build_query($params, '', '&amp;');
        }

        return $adminBase;
    }
}
