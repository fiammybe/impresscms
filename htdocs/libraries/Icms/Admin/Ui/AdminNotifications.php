<?php

declare(strict_types=1);

namespace Icms\Admin\Ui;

/**
 * Admin notification helpers for the ImpressCMS control panel.
 *
 * Renders Bootstrap-compatible notification banners (success / warning /
 * error / info) that can be displayed inline in admin pages or stored in the
 * session for display after a redirect.
 *
 * @copyright The ImpressCMS Project <https://www.impresscms.org/>
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package   Icms\Admin\Ui
 * @since     2.0
 */
class AdminNotifications
{
    /**
     * Render a success notification banner.
     *
     * @param string $message Human-readable message text.
     * @param bool   $session When `true` the message is stored in the session
     *                        for display after the next redirect rather than
     *                        being echoed immediately.
     *
     * @return void
     */
    public function success(string $message, bool $session = false): void
    {
        $this->notify($message, 'success', $session);
    }

    /**
     * Render a warning notification banner.
     *
     * @param string $message Human-readable message text.
     * @param bool   $session Store in the session instead of echoing immediately.
     *
     * @return void
     */
    public function warning(string $message, bool $session = false): void
    {
        $this->notify($message, 'warning', $session);
    }

    /**
     * Render an error notification banner.
     *
     * @param string $message Human-readable message text.
     * @param bool   $session Store in the session instead of echoing immediately.
     *
     * @return void
     */
    public function error(string $message, bool $session = false): void
    {
        $this->notify($message, 'danger', $session);
    }

    /**
     * Render an informational notification banner.
     *
     * @param string $message Human-readable message text.
     * @param bool   $session Store in the session instead of echoing immediately.
     *
     * @return void
     */
    public function info(string $message, bool $session = false): void
    {
        $this->notify($message, 'info', $session);
    }

    /**
     * Emit or store a notification.
     *
     * When `$session` is `true` the message is appended to
     * `$_SESSION['redirect_message']` (compatible with the jGrowl mechanism
     * in {@see icms_cp_header()}).  Otherwise it is echoed immediately as a
     * Bootstrap `alert` div.
     *
     * @param string $message Alert body text.
     * @param string $type    Bootstrap alert variant (success|warning|danger|info).
     * @param bool   $session Whether to defer via the session.
     *
     * @return void
     */
    private function notify(string $message, string $type, bool $session): void
    {
        $safeMessage = htmlspecialchars($message, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if ($session) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['redirect_message'] = $safeMessage;

            return;
        }

        $safeType = htmlspecialchars($type, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        echo '<div class="alert alert-' . $safeType . '" role="alert">'
            . $safeMessage
            . '</div>' . PHP_EOL;
    }
}
