<?php

declare(strict_types=1);

namespace Icms\Admin\Ui;

use icms_view_Tpl;

/**
 * Admin template wrapper for the ImpressCMS control panel.
 *
 * Provides a thin, type-safe facade around {@see \icms_view_Tpl} so that
 * admin controllers can render and assign template variables through a
 * consistent OO interface.
 *
 * @copyright The ImpressCMS Project <https://www.impresscms.org/>
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package   Icms\Admin\Ui
 * @since     2.0
 */
class AdminTemplate
{
    /**
     * Underlying Smarty-based template engine.
     *
     * @var \icms_view_Tpl
     */
    private icms_view_Tpl $tpl;

    /**
     * @param \icms_view_Tpl|null $tpl  Inject a pre-configured template instance,
     *                                  or pass `null` to create a fresh one.
     */
    public function __construct(?icms_view_Tpl $tpl = null)
    {
        $this->tpl = $tpl ?? new icms_view_Tpl();
    }

    /**
     * Assign a template variable.
     *
     * @param string $name  Template variable name.
     * @param mixed  $value Value to assign.
     *
     * @return static
     */
    public function assign(string $name, mixed $value): static
    {
        $this->tpl->assign($name, $value);

        return $this;
    }

    /**
     * Render a template resource and return the output as a string.
     *
     * @param string $template Template resource string (e.g. `'db:system_admin.html'`).
     *
     * @return string Rendered output.
     */
    public function render(string $template): string
    {
        return $this->tpl->fetch($template);
    }

    /**
     * Render a template resource and send the output directly to the browser.
     *
     * @param string $template Template resource string.
     *
     * @return void
     */
    public function display(string $template): void
    {
        $this->tpl->display($template);
    }

    /**
     * Return the underlying {@see \icms_view_Tpl} instance.
     *
     * @return \icms_view_Tpl
     */
    public function getTpl(): icms_view_Tpl
    {
        return $this->tpl;
    }
}
