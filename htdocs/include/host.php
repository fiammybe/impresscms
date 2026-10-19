<?php
/**
 * Locates the trust path, the Composer autoloader and the Symfony host.
 *
 * Used before anything else is loaded: by index.php (to hand the request to
 * the host in the trust path), by Icms\Kernel\Bootstrap and icms::setup() (to
 * find vendor/autoload.php) and by the installer. Plain functions without
 * dependencies; mainfile.php is read, never executed, because including it
 * would run include/common.php (or, with $xoopsOption['nocommon'], keep a later
 * include from running it).
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		core
 * @since		2.1
 */

require_once __DIR__ . '/requirements.php';

if (!function_exists('icms_host_trust_path')) {
	/**
	 * The trust path recorded in the site's mainfile.php, or null when the site
	 * is not installed.
	 *
	 * Follows ICMS_MAINFILE from the environment when mainfile.php is the
	 * uninstalled stub, as the stub itself does.
	 *
	 * @param string $root the web root (ICMS_ROOT_PATH)
	 */
	function icms_host_trust_path(string $root): ?string
	{
		return icms_mainfile_trust_path($root);
	}

	/**
	 * The Composer autoloader to load, in order of preference: the trust path's
	 * (installed host), the staging directory's (uploaded but not yet installed)
	 * and the web root's (sites from before the host). Null when there is none.
	 *
	 * @param string      $root  the web root (ICMS_ROOT_PATH)
	 * @param string|null $trust the trust path (ICMS_TRUST_PATH), if known
	 */
	function icms_host_vendor_autoload(string $root, ?string $trust): ?string
	{
		$vendor = icms_vendor_directory($root, $trust);

		return $vendor === null ? null : $vendor . '/autoload.php';
	}

	/**
	 * Whether the trust path lies inside one of the given public directories
	 * (the web root, the document root): then the web server can serve it, and
	 * nginx and Caddy ignore the .htaccess files that would protect it.
	 *
	 * @param string $trust      the trust path
	 * @param string ...$publics public directories; empty values are ignored
	 */
	function icms_host_trust_path_is_public(string $trust, string ...$publics): bool
	{
		$normalize = static function (string $path): string {
			$real = realpath($path);
			$path = rtrim(str_replace('\\', '/', $real !== false ? $real : $path), '/') . '/';

			return DIRECTORY_SEPARATOR === '\\' ? strtolower($path) : $path;
		};

		if (trim($trust) === '') {
			return false;
		}
		$trust = $normalize($trust);
		foreach ($publics as $public) {
			if (trim($public) !== '' && str_starts_with($trust, $normalize($public))) {
				return true;
			}
		}

		return false;
	}

	/**
	 * The script a request routed to index.php actually asked for, for sites
	 * without a host: a web server at routing level 3 sends every PHP request
	 * to index.php, which then runs that script itself, as the server would at
	 * level 2. Null for index.php itself, paths that are not existing PHP
	 * files of the web root, and paths that are never served.
	 *
	 * @param string $root the web root (ICMS_ROOT_PATH)
	 */
	function icms_host_requested_script(string $root): ?string
	{
		$path = rawurldecode((string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH));
		$base = icms_host_base_path($root);
		if ($base !== '' && str_starts_with($path, $base . '/')) {
			$path = substr($path, strlen($base));
		}
		$relative = ltrim($path, '/');
		if ($relative === '' || str_ends_with($relative, '/')) {
			$relative .= 'index.php';
		}

		// the same paths as docs/server/ and htdocs/htaccess.txt never serve
		if (
			$relative === 'index.php'
			|| !preg_match('/\.php$/i', $relative)
			|| str_contains($relative, "\0")
			|| preg_match('#(^|/)\.|(^|/)\.\.(/|$)|^(trust_dist|templates_c|cache|tests|uploads|plugins/waiting|libraries/tcpdf/tools|install/modules/protector/trust_path)(/|$)#i', $relative)
		) {
			return null;
		}

		$realRoot = realpath($root);
		$script = realpath($root . '/' . $relative);
		if ($realRoot === false || $script === false || !is_file($script) || !str_starts_with($script, $realRoot . DIRECTORY_SEPARATOR)) {
			return null;
		}

		return $script;
	}

	/**
	 * The URL path of the web root: '' for a site at the document root, or for
	 * example '/cms'. Taken from the web root's place under DOCUMENT_ROOT, or,
	 * for a web root outside it (an Apache Alias), from SCRIPT_NAME, which is
	 * index.php for a rewritten request.
	 *
	 * @param string $root the web root (ICMS_ROOT_PATH)
	 */
	function icms_host_base_path(string $root): string
	{
		$realRoot = realpath($root);
		$documentRoot = realpath((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''));
		if ($realRoot !== false && $documentRoot !== false && str_starts_with($realRoot . DIRECTORY_SEPARATOR, rtrim($documentRoot, '/\\') . DIRECTORY_SEPARATOR)) {
			return rtrim(str_replace('\\', '/', substr($realRoot, strlen(rtrim($documentRoot, '/\\')))), '/');
		}

		$scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));

		return str_ends_with($scriptName, '/index.php') ? rtrim(dirname($scriptName), '/') : '';
	}

	/**
	 * The host's front controller in the trust path, or null when the site is
	 * not installed or its trust path holds no host.
	 *
	 * @param string $root the web root (ICMS_ROOT_PATH)
	 */
	function icms_host_entry(string $root): ?string
	{
		$trust = icms_host_trust_path($root);
		if ($trust === null || !is_file($trust . '/host.php') || !is_file($trust . '/vendor/autoload.php')) {
			return null;
		}

		return $trust . '/host.php';
	}

	/**
	 * Whether this request reached PHP through the web root's index.php: run by
	 * the host, or, before the host is installed, by index.php itself. ImpressCMS
	 * requires that for every PHP request (routing level 3).
	 */
	function icms_host_routed(): bool
	{
		return defined('ICMS_HOST_AUTOLOADED') || defined('ICMS_ROUTED_BY_INDEX');
	}

	/**
	 * Whether the installer or the system module update may install the host:
	 * this request was routed through index.php, so the web server sends every
	 * PHP request there, or it runs on the command line (bin/test-db, scripted
	 * upgrades), where no web server is involved.
	 */
	function icms_host_installable(): bool
	{
		return icms_host_routed() || PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg';
	}

	/**
	 * The page for a legacy script that the web server executed itself, without
	 * index.php, while the host is installed: session and identity belong to the
	 * host, so the script must not run. Null when the request may proceed: it was
	 * routed, it runs on the command line, or the site has no host yet (the
	 * installer, or an upgrade before the system module update).
	 *
	 * @param string $root   the web root (ICMS_ROOT_PATH)
	 * @param bool   $routed icms_host_routed()
	 * @param string $sapi   PHP_SAPI
	 */
	function icms_host_routing_page(string $root, bool $routed, string $sapi): ?string
	{
		if ($routed || $sapi === 'cli' || $sapi === 'phpdbg' || icms_host_entry($root) === null) {
			return null;
		}

		$page = @file_get_contents(rtrim($root, '/\\') . '/routing.html');

		return $page !== false ? $page : '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Web server configuration needed</title></head><body>'
			. '<p>ImpressCMS needs every PHP request to be sent to index.php. ' . icms_host_routing_advice('unsupported') . '</p></body></html>';
	}

	/**
	 * Sends the routing page and stops for a directly executed legacy script
	 * (see icms_host_routing_page()). Status 503: the site works again as soon
	 * as the web server is configured.
	 *
	 * @param string $root the web root (ICMS_ROOT_PATH)
	 */
	function icms_host_require_routing(string $root): void
	{
		$page = icms_host_routing_page($root, icms_host_routed(), PHP_SAPI);
		if ($page === null) {
			return;
		}

		if (!headers_sent()) {
			header('HTTP/1.1 503 Service Unavailable', true, 503);
			header('Retry-After: 3600');
			header('Content-Type: text/html; charset=utf-8');
			header('Cache-Control: no-store');
		}
		echo $page;
		exit;
	}

	/**
	 * Writes the web root's .htaccess from htaccess.txt on servers that read
	 * .htaccess files (Apache, LiteSpeed), when the web root has none. An
	 * existing .htaccess is never changed.
	 *
	 * @param string $root           the web root (ICMS_ROOT_PATH)
	 * @param string $serverSoftware $_SERVER['SERVER_SOFTWARE']
	 * @return string 'written', 'exists', 'unsupported' (another server) or 'failed'
	 */
	function icms_host_write_htaccess(string $root, string $serverSoftware): string
	{
		$root = rtrim($root, '/\\');
		if (!preg_match('/apache|litespeed/i', $serverSoftware)) {
			return 'unsupported';
		}
		if (file_exists($root . '/.htaccess')) {
			return 'exists';
		}
		$rules = @file_get_contents($root . '/htaccess.txt');

		return $rules !== false && @file_put_contents($root . '/.htaccess', $rules, LOCK_EX) !== false ? 'written' : 'failed';
	}

	/**
	 * What the administrator has to do so that every PHP request reaches
	 * index.php, after icms_host_write_htaccess() returned $status. Plain
	 * English HTML, for the installer, the system module update and routing.html.
	 *
	 * @param string $status a result of icms_host_write_htaccess()
	 */
	function icms_host_routing_advice(string $status): string
	{
		switch ($status) {
			case 'written':
				return 'ImpressCMS has written <code>.htaccess</code> from <code>htaccess.txt</code>. Reload this page. If this message stays, the web server ignores the rewrite rules: ask your hosting provider to enable <code>mod_rewrite</code> and <code>AllowOverride</code>.';
			case 'exists':
				return 'Your web root already has a <code>.htaccess</code>. Add the <code>&lt;IfModule mod_rewrite.c&gt;</code> block of <code>htaccess.txt</code> to it, then reload this page.';
			case 'failed':
				return 'Rename <code>htaccess.txt</code> in the web root to <code>.htaccess</code>, then reload this page.';
			default:
				return 'Configure the web server to send every PHP request to <code>index.php</code>: for Apache, rename <code>htaccess.txt</code> to <code>.htaccess</code>; for nginx, FrankenPHP or Caddy, use the <a href="https://github.com/ImpressCMS/impresscms/tree/main/docs/server">example configurations</a>. Then reload this page.';
		}
	}
}
