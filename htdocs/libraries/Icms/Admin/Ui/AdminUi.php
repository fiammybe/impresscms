<?php

declare(strict_types=1);

namespace Icms\Admin\Ui;

/**
 * Admin UI helpers – renders the control-panel header, footer and inline
 * message banners.
 *
 * @copyright The ImpressCMS Project <https://www.impresscms.org/>
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package   Icms\Admin\Ui
 * @since     2.0
 */
class AdminUi
{
    /**
     * Render the admin control-panel header.
     *
     * Delegates to the legacy {@see icms_cp_header()} implementation so that
     * all theme / template wiring is preserved while callers can already use
     * the new class-based API.
     *
     * @return void
     */
    public function renderHeader(): void
    {
        icms_cp_header();
    }

    /**
     * Render the admin control-panel footer.
     *
     * Delegates to the legacy {@see icms_cp_footer()} implementation.
     *
     * @return void
     */
    public function renderFooter(): void
    {
        icms_cp_footer();
    }

    /**
     * Display a generic informational message inside the admin panel.
     *
     * @param string $message Human-readable message text.
     * @param string $type    Bootstrap-compatible alert type: info|success|warning|danger.
     *
     * @return void
     */
    public function showMessage(string $message, string $type = 'info'): void
    {
        $this->renderAlert($message, $type);
    }

    /**
     * Display a warning message inside the admin panel.
     *
     * @param string $message Human-readable warning text.
     *
     * @return void
     */
    public function showWarning(string $message): void
    {
        $this->renderAlert($message, 'warning');
    }

    /**
     * Display an error message inside the admin panel.
     *
     * @param string $message Human-readable error text.
     *
     * @return void
     */
    public function showError(string $message): void
    {
        $this->renderAlert($message, 'danger');
    }

    /**
     * Output an inline Bootstrap-style alert banner.
     *
     * @param string $message Alert body text (HTML-escaped internally).
     * @param string $type    Bootstrap alert variant.
     *
     * @return void
     */
    private function renderAlert(string $message, string $type): void
    {
        $safeMessage = htmlspecialchars($message, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $safeType    = htmlspecialchars($type, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        echo '<div class="alert alert-' . $safeType . '" role="alert">'
            . $safeMessage
            . '</div>' . PHP_EOL;
    }
}
