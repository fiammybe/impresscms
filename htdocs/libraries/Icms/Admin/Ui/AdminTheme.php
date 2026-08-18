<?php

declare(strict_types=1);

namespace Icms\Admin\Ui;

/**
 * Admin theme asset injector for the ImpressCMS control panel.
 *
 * Queues CSS and JS assets on the active `$xoTheme` / `$icmsTheme` object so
 * that modules can add admin-specific styles and scripts without reaching into
 * global scope.
 *
 * @copyright The ImpressCMS Project <https://www.impresscms.org/>
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package   Icms\Admin\Ui
 * @since     2.0
 */
class AdminTheme
{
    /**
     * Retrieve the active theme object from the global scope.
     *
     * @return \icms_view_theme_Base|null
     */
    private function getTheme(): mixed
    {
        return $GLOBALS['xoTheme'] ?? $GLOBALS['icmsTheme'] ?? null;
    }

    /**
     * Enqueue a stylesheet on the active admin theme.
     *
     * @param string               $url        Absolute or site-root-relative stylesheet URL.
     * @param array<string, mixed> $attributes Optional HTML attributes (e.g. `['media' => 'screen']`).
     *
     * @return void
     */
    public function addCss(string $url, array $attributes = []): void
    {
        $theme = $this->getTheme();

        if ($theme !== null && method_exists($theme, 'addStylesheet')) {
            $theme->addStylesheet($url, $attributes);
        }
    }

    /**
     * Enqueue a JavaScript file on the active admin theme.
     *
     * @param string               $url        Absolute or site-root-relative script URL.
     * @param array<string, mixed> $attributes Optional HTML attributes (e.g. `['type' => 'text/javascript']`).
     * @param string               $inline     Optional inline script body to inject instead of a `src` URL.
     *
     * @return void
     */
    public function addJs(string $url, array $attributes = [], string $inline = ''): void
    {
        $theme = $this->getTheme();

        if ($theme !== null && method_exists($theme, 'addScript')) {
            $theme->addScript($url, $attributes, $inline);
        }
    }
}
