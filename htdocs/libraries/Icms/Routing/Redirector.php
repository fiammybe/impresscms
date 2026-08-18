<?php

declare(strict_types=1);

namespace Icms\Routing;

/**
 * Admin redirect helper for the ImpressCMS control panel.
 *
 * Provides a clean, type-safe alternative to `header('Location: ...')` calls
 * scattered throughout admin pages.  Optionally stores a flash message in the
 * session that {@see icms_cp_header()} will pick up and display via jGrowl.
 *
 * @copyright The ImpressCMS Project <https://www.impresscms.org/>
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package   Icms\Routing
 * @since     2.0
 */
class Redirector
{
    /**
     * Redirect the browser to `$url` and optionally set a session flash message.
     *
     * This method sends the `Location` header and terminates the current
     * script execution.
     *
     * @param string $url     Absolute or root-relative target URL.
     * @param string $message Optional flash message to display after the redirect.
     *                        The message is stored in `$_SESSION['redirect_message']`
     *                        and is displayed by the jGrowl integration in
     *                        {@see icms_cp_header()}.
     * @param int    $status  HTTP status code (default: 302 Found).
     *
     * @return never
     */
    public function redirect(string $url, string $message = '', int $status = 302): never
    {
        if ($message !== '') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['redirect_message'] = htmlspecialchars(
                $message,
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            );
        }

        if (!headers_sent()) {
            header('Location: ' . $url, true, $status);
        } else {
            // Fallback for contexts where headers were already sent.
            echo '<script>window.location=' . json_encode($url) . ';</script>';
        }

        exit();
    }
}
