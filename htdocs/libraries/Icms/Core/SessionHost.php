<?php
/**
 * The session and the authentication of the application that hosts ImpressCMS
 *
 * When ImpressCMS runs inside the Symfony host, the host starts and stores the
 * session and keeps the logged-in user as its own security token. It installs
 * an implementation with Icms\Core\Session::setHost(); Icms\Core\Session then
 * uses it instead of its own save handler and of $_SESSION['xoopsUserId'].
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		Core
 * @subpackage	Session
 * @since		3.0
 */

namespace Icms\Core;

interface SessionHost {

	/**
	 * Starts the host's session; $_SESSION holds its data afterwards.
	 */
	public function start(): void;

	/**
	 * The id of the authenticated user, or 0 for an anonymous visitor.
	 */
	public function userId(): int;

	/**
	 * Authenticates the user for this and later requests, with a new session id.
	 */
	public function login(int $uid): void;

	/**
	 * Ends the authentication and invalidates the session.
	 */
	public function logout(): void;
}
