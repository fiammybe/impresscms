<?php

declare(strict_types=1);

namespace Icms\Admin\Ui;

/**
 * Admin navigation helpers – manages and renders the breadcrumb trail and the
 * side/top navigation items in the ImpressCMS control panel.
 *
 * @copyright The ImpressCMS Project <https://www.impresscms.org/>
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package   Icms\Admin\Ui
 * @since     2.0
 */
class AdminNavigation
{
    /**
     * Accumulated breadcrumb segments.
     *
     * Each element is an associative array with keys:
     *   - `title` (string)  Human-readable label.
     *   - `url`   (string)  Target URL, or empty string for the active (last) item.
     *
     * @var array<int, array{title: string, url: string}>
     */
    private array $breadcrumbs = [];

    /**
     * Accumulated navigation items.
     *
     * Each element is an associative array with keys:
     *   - `title` (string)  Human-readable label.
     *   - `url`   (string)  Target URL.
     *   - `icon`  (string)  Optional icon class or image path.
     *
     * @var array<int, array{title: string, url: string, icon: string}>
     */
    private array $navItems = [];

    /**
     * Add a breadcrumb segment.
     *
     * Pass an empty string for `$url` when this is the active (last) segment.
     *
     * @param string $title Human-readable label.
     * @param string $url   Link target, or empty string for the active item.
     *
     * @return static
     */
    public function breadcrumb(string $title, string $url = ''): static
    {
        $this->breadcrumbs[] = ['title' => $title, 'url' => $url];

        return $this;
    }

    /**
     * Register a navigation item.
     *
     * @param string $title Human-readable label.
     * @param string $url   Link target.
     * @param string $icon  Optional icon class or image path.
     *
     * @return static
     */
    public function addNavigationItem(string $title, string $url, string $icon = ''): static
    {
        $this->navItems[] = ['title' => $title, 'url' => $url, 'icon' => $icon];

        return $this;
    }

    /**
     * Render the current navigation state as HTML.
     *
     * Outputs:
     *  1. A Bootstrap-compatible `<ol class="breadcrumb">` trail (when breadcrumbs
     *     have been registered).
     *  2. A `<ul class="nav">` list of navigation items (when any have been added).
     *
     * @return string Rendered HTML fragment.
     */
    public function renderNavigation(): string
    {
        $html = '';

        if (!empty($this->breadcrumbs)) {
            $html .= '<ol class="breadcrumb">' . PHP_EOL;
            foreach ($this->breadcrumbs as $crumb) {
                $title = htmlspecialchars($crumb['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if ($crumb['url'] !== '') {
                    $url   = htmlspecialchars($crumb['url'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $html .= '  <li class="breadcrumb-item"><a href="' . $url . '">' . $title . '</a></li>' . PHP_EOL;
                } else {
                    $html .= '  <li class="breadcrumb-item active" aria-current="page">' . $title . '</li>' . PHP_EOL;
                }
            }
            $html .= '</ol>' . PHP_EOL;
        }

        if (!empty($this->navItems)) {
            $html .= '<ul class="nav">' . PHP_EOL;
            foreach ($this->navItems as $item) {
                $title = htmlspecialchars($item['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $url   = htmlspecialchars($item['url'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $icon  = '';
                if ($item['icon'] !== '') {
                    $iconEsc = htmlspecialchars($item['icon'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $icon    = '<i class="' . $iconEsc . '"></i> ';
                }
                $html .= '  <li class="nav-item"><a class="nav-link" href="' . $url . '">'
                    . $icon . $title . '</a></li>' . PHP_EOL;
            }
            $html .= '</ul>' . PHP_EOL;
        }

        return $html;
    }

    /**
     * Return the registered breadcrumb segments.
     *
     * @return array<int, array{title: string, url: string}>
     */
    public function getBreadcrumbs(): array
    {
        return $this->breadcrumbs;
    }

    /**
     * Return the registered navigation items.
     *
     * @return array<int, array{title: string, url: string, icon: string}>
     */
    public function getNavItems(): array
    {
        return $this->navItems;
    }
}
