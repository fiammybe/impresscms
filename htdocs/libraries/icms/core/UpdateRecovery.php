<?php
/**
 * ImpressCMS Core Update Recovery
 *
 * Keeps a record of the files a core update writes, and installs a stand-alone recovery script
 * that can undo the update when the new code breaks the site.
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @category	ICMS
 * @package		Core
 * @subpackage	Update
 * @since		2.0
 */

defined('ICMS_ROOT_PATH') or die("ImpressCMS root path not defined");

/**
 * Update record and recovery script
 *
 * The update record ("manifest") is a JSON file next to the backup in <trust path>/backups. It lists
 * the files the update adds, replaces and deletes, and is written before any file is changed, so it
 * also covers an update that stopped halfway.
 *
 * The recovery script is written to the web root before the update changes any file. It does not
 * load any ImpressCMS code, so it keeps working when the new core is broken. It asks for the recovery
 * password the administrator chose in the update form, so they know it even when the page after the
 * update fails to load. Only a password_hash() of it is stored, in <trust path>/updates/recovery.json. The script expires after a week, and can be removed from
 * the version checker page once the update is known to work.
 */
class icms_core_UpdateRecovery {

	/**
	 * File name of the recovery script in the web root
	 */
	const SCRIPT_NAME = 'update-recovery.php';

	/**
	 * How long the recovery script stays usable, in seconds
	 */
	const LIFETIME = 604800;

	/**
	 * Minimum length of a recovery password
	 */
	const MIN_PASSWORD_LENGTH = 8;

	/**
	 * Errors
	 * @var array
	 */
	public $errors = array();

	/**
	 * Path of the recovery script
	 *
	 * @return string
	 */
	public static function getScriptPath() {
		return ICMS_ROOT_PATH . '/' . self::SCRIPT_NAME;
	}

	/**
	 * URL of the recovery script
	 *
	 * @return string
	 */
	public static function getScriptUrl() {
		return (defined('ICMS_URL') ? ICMS_URL : '') . '/' . self::SCRIPT_NAME;
	}

	/**
	 * Path of the recovery configuration in the trust path
	 *
	 * @return string|false
	 */
	public static function getConfigPath() {
		$dir = icms_core_Backup::getPrivateDir('updates');
		return $dir === false ? false : $dir . '/recovery.json';
	}

	/**
	 * Path of the update record that belongs to a backup
	 *
	 * @param string $backupPath Path of the backup zip
	 * @return string
	 */
	public static function getManifestPath($backupPath) {
		return preg_replace('/\.zip$/i', '', $backupPath) . '.json';
	}

	/**
	 * Write an update record
	 *
	 * The file is written to a temporary name first and then renamed, so a half-written record
	 * never replaces a complete one.
	 *
	 * @param string $path Path of the record
	 * @param array $data Record
	 * @return bool
	 */
	public static function writeManifest($path, array $data) {
		$json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		if ($json === false) {
			return false;
		}
		$tmp = $path . '.tmp';
		if (file_put_contents($tmp, $json) === false) {
			return false;
		}
		@chmod($tmp, 0640);
		return rename($tmp, $path);
	}

	/**
	 * Read an update record
	 *
	 * @param string $path Path of the record
	 * @return array|null
	 */
	public static function readManifest($path) {
		if (!is_file($path)) {
			return null;
		}
		$data = json_decode((string) file_get_contents($path), true);
		return is_array($data) ? $data : null;
	}

	/**
	 * Install the recovery script for an update
	 *
	 * @param string $manifestPath Path of the update record
	 * @param string $backupPath Path of the backup taken before the update
	 * @param string|null $key The recovery password. When null, a random one is created and
	 *                         returned in this variable, to show to the administrator.
	 * @return bool
	 */
	public function install($manifestPath, $backupPath, &$key) {
		$configPath = self::getConfigPath();
		if ($configPath === false) {
			$this->errors[] = "The recovery script needs a trust path, but no trust path is defined (ICMS_TRUST_PATH).";
			return false;
		}

		if ($key === null || $key === '') {
			try {
				$key = bin2hex(random_bytes(16));
			} catch (Exception $e) {
				$this->errors[] = "Could not create a recovery password: " . $e->getMessage();
				return false;
			}
		} elseif (strlen($key) < self::MIN_PASSWORD_LENGTH) {
			$this->errors[] = "The recovery password must be at least " . self::MIN_PASSWORD_LENGTH . " characters long.";
			return false;
		}

		$config = array(
			'key_hash' => password_hash($key, PASSWORD_DEFAULT),
			'created' => time(),
			'expires' => time() + self::LIFETIME,
			'manifest' => $manifestPath,
			'backup' => $backupPath,
			'root' => ICMS_ROOT_PATH,
			'clear' => array(
				defined('ICMS_CACHE_PATH') ? ICMS_CACHE_PATH : ICMS_ROOT_PATH . '/cache',
				defined('ICMS_COMPILE_PATH') ? ICMS_COMPILE_PATH : ICMS_ROOT_PATH . '/templates_c',
			),
		);
		if (!self::writeManifest($configPath, $config)) {
			$this->errors[] = "Could not write the recovery configuration: " . $configPath;
			return false;
		}

		$script = str_replace(
			array("'__RECOVERY_CONFIG__'", '__RECOVERY_CREATED__'),
			array(var_export($configPath, true), date('Y-m-d H:i:s')),
			self::getTemplate()
		);
		$scriptPath = self::getScriptPath();
		if (file_put_contents($scriptPath, $script) === false || !is_file($scriptPath)) {
			@unlink($configPath);
			$this->errors[] = "Could not write the recovery script: " . $scriptPath;
			return false;
		}
		@chmod($scriptPath, 0644);
		return true;
	}

	/**
	 * Remove the recovery script and its configuration
	 *
	 * @return bool True when nothing is left behind
	 */
	public static function remove() {
		$ok = true;
		$scriptPath = self::getScriptPath();
		if (file_exists($scriptPath) && !@unlink($scriptPath)) {
			$ok = false;
		}
		$configPath = self::getConfigPath();
		if ($configPath !== false && file_exists($configPath) && !@unlink($configPath)) {
			$ok = false;
		}
		return $ok;
	}

	/**
	 * Get the state of the installed recovery script
	 *
	 * An expired script is removed.
	 *
	 * @return array|null Null when no recovery script is installed
	 */
	public static function getStatus() {
		$configPath = self::getConfigPath();
		$config = $configPath === false ? null : self::readManifest($configPath);
		$scriptExists = file_exists(self::getScriptPath());

		if (!$config || !$scriptExists) {
			if ($config || $scriptExists) {
				// a half-installed recovery script can never be used: clean it up
				self::remove();
			}
			return null;
		}
		if (time() > (int) $config['expires']) {
			self::remove();
			return null;
		}

		$manifest = isset($config['manifest']) ? self::readManifest($config['manifest']) : null;
		return array(
			'url' => self::getScriptUrl(),
			'expires' => (int) $config['expires'],
			'expires_formatted' => date('Y-m-d H:i', (int) $config['expires']),
			'backup' => isset($config['backup']) ? basename($config['backup']) : '',
			'status' => $manifest && isset($manifest['status']) ? $manifest['status'] : 'unknown',
		);
	}

	/**
	 * The recovery script
	 *
	 * It must not use any ImpressCMS code: it has to work when the updated core is broken.
	 *
	 * @return string
	 */
	private static function getTemplate() {
		return <<<'RECOVERY'
<?php
/**
 * ImpressCMS update recovery
 *
 * Written by the ImpressCMS core updater on __RECOVERY_CREATED__. It undoes the last core update
 * with the backup and the update record in the trust path. It does not load any ImpressCMS code,
 * so it works when the updated site does not.
 *
 * It needs the recovery password that was chosen when the update ran, and stops working after a week.
 * Delete this file when the site works.
 */

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', '0');
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, private');
header('X-Robots-Tag: noindex, nofollow');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; form-action 'self'");

$configFile = '__RECOVERY_CONFIG__';

function rc_h($s) {
	return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function rc_page($title, $body) {
	echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
		. '<meta name="robots" content="noindex, nofollow"><title>' . rc_h($title) . '</title>'
		. '<style>body{font-family:system-ui,sans-serif;max-width:46rem;margin:2rem auto;padding:0 1rem;line-height:1.5;color:#222;background:#fff}'
		. 'h1{font-size:1.4rem}.box{border:1px solid #ccc;border-radius:6px;padding:1rem;margin:1rem 0}.err{border-color:#c00;background:#fff3f3}'
		. '.ok{border-color:#080;background:#f3fff3}input[type=password],input[type=text]{width:100%;padding:.4rem;font-family:monospace;box-sizing:border-box}'
		. 'button{padding:.5rem 1rem;margin-top:.5rem}code{background:#f3f3f3;padding:0 .2rem}ul{padding-left:1.2rem}</style></head><body>'
		. '<h1>' . rc_h($title) . '</h1>' . $body . '</body></html>';
	exit();
}

function rc_remove_self($configFile) {
	@unlink($configFile);
	@unlink(__FILE__);
}

function rc_safe($path) {
	$path = str_replace('\\', '/', (string) $path);
	if ($path === '' || strpos($path, "\0") !== false || $path[0] === '/' || preg_match('#^[a-zA-Z]:#', $path)) {
		return false;
	}
	return !in_array('..', explode('/', $path), true);
}

function rc_list($items, $max = 50) {
	if (!$items) {
		return '';
	}
	$out = '<ul>';
	foreach (array_slice($items, 0, $max) as $item) {
		$out .= '<li><code>' . rc_h($item) . '</code></li>';
	}
	if (count($items) > $max) {
		$out .= '<li>... and ' . (count($items) - $max) . ' more</li>';
	}
	return $out . '</ul>';
}

function rc_key_form($message = '') {
	rc_page('ImpressCMS update recovery',
		($message ? '<div class="box err">' . rc_h($message) . '</div>' : '')
		. '<p>This page undoes the last ImpressCMS core update. Enter the recovery password that was chosen when the update was started.</p>'
		. '<form method="post"><label for="key">Recovery password</label><input type="password" name="key" id="key" autocomplete="off" required>'
		. '<button type="submit">Continue</button></form>'
		. '<p>Lost the password? You can also restore the site by hand: see "Restoring a backup by hand" in '
		. '<code>libraries/icms/core/UPDATE_README.md</code>.</p>');
}

function rc_restore(array $config, array $manifest) {
	$result = array('restored' => 0, 'removed' => 0, 'failed' => array(), 'missing' => array(), 'cleared' => 0);
	$root = rtrim(str_replace('\\', '/', $config['root']), '/');

	if (!class_exists('ZipArchive')) {
		$result['failed'][] = 'The PHP zip extension (ZipArchive) is not available.';
		return $result;
	}
	$zip = new ZipArchive();
	if ($zip->open($config['backup']) !== true) {
		$result['failed'][] = 'The backup could not be opened: ' . basename($config['backup']);
		return $result;
	}

	// put back every file the update replaced or deleted
	$fromBackup = array_merge(
		isset($manifest['replaced']) ? $manifest['replaced'] : array(),
		isset($manifest['deleted']) ? $manifest['deleted'] : array()
	);
	foreach ($fromBackup as $relative) {
		if (!rc_safe($relative)) {
			continue;
		}
		$data = $zip->getFromName($relative);
		if ($data === false) {
			$result['missing'][] = $relative;
			continue;
		}
		$target = $root . '/' . $relative;
		$dir = dirname($target);
		if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
			$result['failed'][] = $relative;
			continue;
		}
		if (file_put_contents($target, $data) === false) {
			$result['failed'][] = $relative;
			continue;
		}
		$result['restored']++;
	}
	$zip->close();

	// remove the files the update added, and the directories that are empty after that
	foreach (isset($manifest['added']) ? $manifest['added'] : array() as $relative) {
		if (!rc_safe($relative)) {
			continue;
		}
		$target = $root . '/' . $relative;
		if (is_file($target)) {
			if (@unlink($target)) {
				$result['removed']++;
			} else {
				$result['failed'][] = $relative . ' (could not delete)';
				continue;
			}
		}
		$dir = dirname($target);
		while (strlen($dir) > strlen($root) && strpos($dir, $root . '/') === 0 && @rmdir($dir)) {
			$dir = dirname($dir);
		}
	}

	// cached and compiled files may come from the new version
	foreach (isset($config['clear']) ? $config['clear'] : array() as $dir) {
		foreach ((array) glob(rtrim($dir, '/') . '/*') as $file) {
			if (is_file($file) && !in_array(basename($file), array('index.html', '.htaccess'), true) && @unlink($file)) {
				$result['cleared']++;
			}
		}
	}
	return $result;
}

// The recovery configuration lives in the trust path
$config = is_file($configFile) ? json_decode((string) file_get_contents($configFile), true) : null;
if (!is_array($config) || empty($config['key_hash'])) {
	@unlink(__FILE__);
	rc_page('Recovery not available', '<p>This recovery script is no longer valid and has been removed.</p>');
}
if (time() > (int) $config['expires']) {
	rc_remove_self($configFile);
	rc_page('Recovery expired', '<p>This recovery script has expired and has been removed. To undo the update, restore the backup by hand.</p>');
}

$key = isset($_POST['key']) ? trim((string) $_POST['key']) : '';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $key === '') {
	rc_key_form();
}
if (!password_verify($key, (string) $config['key_hash'])) {
	sleep(2); // slow down guessing
	rc_key_form('That is not the recovery password for this update.');
}

$manifest = is_file($config['manifest']) ? json_decode((string) file_get_contents($config['manifest']), true) : null;
if (!is_array($manifest)) {
	rc_page('Update record missing', '<div class="box err">The update record <code>' . rc_h(basename($config['manifest']))
		. '</code> could not be read, so this script cannot tell which files to restore. Restore the backup <code>'
		. rc_h(basename($config['backup'])) . '</code> by hand.</div>');
}
if (!is_file($config['backup'])) {
	rc_page('Backup missing', '<div class="box err">The backup <code>' . rc_h(basename($config['backup']))
		. '</code> is no longer in the trust path, so the update cannot be undone with this script.</div>');
}

$keyField = '<input type="hidden" name="key" value="' . rc_h($key) . '">';
$action = isset($_POST['action']) ? (string) $_POST['action'] : '';
$count = function ($name) use ($manifest) {
	return isset($manifest[$name]) ? count($manifest[$name]) : 0;
};

if ($action === 'remove') {
	rc_remove_self($configFile);
	rc_page('Recovery script removed', '<div class="box ok">The recovery script has been removed. The update was kept as it is.</div>');
}

if ($action === 'restore' && isset($_POST['confirm']) && $_POST['confirm'] === '1') {
	$result = rc_restore($config, $manifest);
	$ok = !$result['failed'];
	$manifest['status'] = $ok ? 'restored' : 'restore_failed';
	$manifest['restored_at'] = date('c');
	@file_put_contents($config['manifest'], json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

	$body = '<div class="box ' . ($ok ? 'ok' : 'err') . '"><strong>'
		. ($ok ? 'The update has been undone.' : 'The update was only partly undone.') . '</strong><br>'
		. $result['restored'] . ' files restored from the backup, ' . $result['removed'] . ' files added by the update removed, '
		. $result['cleared'] . ' cached files cleared.</div>';
	if ($result['failed']) {
		$body .= '<p>These could not be restored. Copy them from the backup <code>' . rc_h(basename($config['backup']))
			. '</code> in the trust path by hand:</p>' . rc_list($result['failed']);
	}
	if ($result['missing']) {
		$body .= '<p>These files were changed by the update but are not in the backup, so they were left as they are. '
			. 'The backup skips files over 50 MB and temporary files such as <code>*.log</code>, <code>*.tmp</code>, '
			. '<code>Thumbs.db</code> and <code>.DS_Store</code>:</p>' . rc_list($result['missing']);
	}
	$body .= '<div class="box"><strong>Database</strong><br>This script restores files only. If you opened the administration '
		. 'area after the update and the System module was updated, the database was changed too. If the site still shows errors, '
		. 'restore the database backup your hosting provider made before the update.</div>'
		. '<p>Check the site now. When it works, remove this recovery script:</p>'
		. '<form method="post">' . $keyField . '<input type="hidden" name="action" value="remove"><button type="submit">Remove the recovery script</button></form>';
	rc_page('ImpressCMS update recovery', $body);
}

$status = isset($manifest['status']) ? (string) $manifest['status'] : 'unknown';
$statusText = array(
	'in_progress' => 'The update stopped while files were being copied, so the site may be partly updated.',
	'completed' => 'All files of the update were installed.',
	'failed' => 'The update reported errors while copying files, so the site may be partly updated.',
	'restored' => 'This update has already been undone. Undoing it again restores the same files.',
	'restore_failed' => 'An earlier attempt to undo this update did not finish.',
);
rc_page('ImpressCMS update recovery',
	'<div class="box"><strong>Last update</strong><br>'
	. 'From ' . rc_h(isset($manifest['from_version']) ? $manifest['from_version'] : '?')
	. ' to ' . rc_h(isset($manifest['to_version']) ? $manifest['to_version'] : '?')
	. ', started ' . rc_h(isset($manifest['started']) ? $manifest['started'] : '?') . '<br>'
	. rc_h(isset($statusText[$status]) ? $statusText[$status] : 'Status: ' . $status) . '<br>'
	. 'Backup: <code>' . rc_h(basename($config['backup'])) . '</code></div>'
	. '<p>Undoing the update will:</p><ul>'
	. '<li>restore ' . ($count('replaced') + $count('deleted')) . ' files from the backup,</li>'
	. '<li>remove ' . $count('added') . ' files that the update added,</li>'
	. '<li>clear the cache and the compiled templates.</li></ul>'
	. '<p>Your configuration (<code>mainfile.php</code>), uploads and the database are not changed.</p>'
	. '<form method="post">' . $keyField
	. '<input type="hidden" name="action" value="restore">'
	. '<label><input type="checkbox" name="confirm" value="1" required> I want to undo the update</label><br>'
	. '<button type="submit">Undo the update</button></form>'
	. '<p>If the update works and you do not need this page any more:</p>'
	. '<form method="post">' . $keyField . '<input type="hidden" name="action" value="remove"><button type="submit">Remove the recovery script</button></form>'
	. '<p>This page stops working on ' . rc_h(date('Y-m-d H:i', (int) $config['expires'])) . '.</p>');
RECOVERY;
	}
}
