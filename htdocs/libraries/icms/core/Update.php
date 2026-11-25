<?php
/**
 * ImpressCMS Core Update Class
 *
 * Handles downloading, verifying, and installing core updates
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @category	ICMS
 * @package		Core
 * @subpackage	Update
 * @since		2.0
 * @author		ImpressCMS Core Team
 */

defined('ICMS_ROOT_PATH') or die("ImpressCMS root path not defined");

/**
 * Core update management class
 *
 * Provides functionality to download, verify, and install ImpressCMS core updates
 */
class icms_core_Update {

	/**
	 * Version checker instance
	 * @var icms_core_Versionchecker
	 */
	private $versionChecker;

	/**
	 * Backup instance
	 * @var icms_core_Backup
	 */
	private $backup;

	/**
	 * Errors array
	 * @var array
	 */
	public $errors = array();

	/**
	 * Update status messages
	 * @var array
	 */
	public $messages = array();

	/**
	 * Temporary directory for downloads
	 * @var string
	 */
	private $tempDir;

	/**
	 * Downloaded file path
	 * @var string
	 */
	private $downloadedFile;

	/**
	 * Paths, relative to the web root, that the updater never writes or deletes.
	 * A trailing slash protects a directory and everything in it.
	 * @var array
	 */
	private $protectedPaths = array(
		'mainfile.php',   // site configuration written by the installer
		'install/',       // the installer is removed after installation and must not come back
		'cache/',
		'templates_c/',
		'uploads/',
	);

	/**
	 * Files that hold site-specific changes: only installed when the site does not have them yet
	 * @var array
	 */
	private $keepExisting = array('robots.txt', 'favicon.ico', 'htaccess.txt');

	/**
	 * Files a package must contain
	 * @var array
	 */
	private $requiredFiles = array('index.php', 'include/version.php', 'include/common.php');

	/**
	 * Name of the list of deleted files in packages built with bin/build-package --from
	 * @var string
	 */
	private $deletedFilesList = 'DELETED-FILES.txt';

	/**
	 * File list of the last installation attempt
	 * @var array|null
	 */
	private $fileList = null;

	/**
	 * Constructor
	 *
	 * @param icms_core_Versionchecker $versionChecker Version checker instance
	 * @param icms_core_Backup $backup Backup instance (optional)
	 */
	public function __construct($versionChecker = null, $backup = null) {
		if ($versionChecker === null) {
			$this->versionChecker = icms_core_Versioncheckergithub::getInstance();
		} else {
			$this->versionChecker = $versionChecker;
		}

		if ($backup === null) {
			$this->backup = new icms_core_Backup();
		} else {
			$this->backup = $backup;
		}

		// Downloads and extracted packages are kept outside the web root, in the trust path
		$this->tempDir = icms_core_Backup::getPrivateDir('updates');

		// Ensure directories exist
		$this->createDirectories();
	}

	/**
	 * Create necessary directories
	 *
	 * @return bool
	 */
	private function createDirectories() {
		if ($this->tempDir === false) {
			$this->errors[] = "Updates are downloaded to the trust path, but no trust path is defined (ICMS_TRUST_PATH).";
			return false;
		}
		return icms_core_Backup::preparePrivateDir($this->tempDir, $this->errors, "update download directory");
	}

	/**
	 * Check if an update is available
	 *
	 * @return bool
	 */
	public function isUpdateAvailable() {
		return $this->versionChecker->check() && $this->versionChecker->hasUpdate();
	}

	/**
	 * Get the download URL for the latest version
	 *
	 * @return string|false
	 */
	public function getDownloadUrl() {
		if (!$this->isUpdateAvailable()) {
			return false;
		}

		$latest = $this->versionChecker->getLatest();
		return isset($latest['url']) ? $latest['url'] : false;
	}

	/**
	 * Download the latest release
	 *
	 * @param string $expectedHash Optional hash to verify against
	 * @return bool
	 */
	public function downloadUpdate($expectedHash = null) {
		$downloadUrl = $this->getDownloadUrl();
		if (!$downloadUrl) {
			$this->errors[] = "No download URL available";
			return false;
		}

		$latest = $this->versionChecker->getLatest();
		$filename = 'impresscms-' . $latest['version'] . '.zip';
		$this->downloadedFile = $this->tempDir . '/' . $filename;

		$this->messages[] = "Starting download from: " . $downloadUrl;

		// Create stream context with user agent
		$context = stream_context_create([
			'http' => [
				'method' => 'GET',
				'header' => [
					'User-Agent: ImpressCMS-Updater/1.0'
				],
				'timeout' => 300 // 5 minutes timeout
			]
		]);

		// Download the file
		$downloadData = file_get_contents($downloadUrl, false, $context);
		
		if ($downloadData === false) {
			$this->errors[] = "Failed to download update from: " . $downloadUrl;
			return false;
		}

		// Save the downloaded file
		if (file_put_contents($this->downloadedFile, $downloadData) === false) {
			$this->errors[] = "Failed to save downloaded file to: " . $this->downloadedFile;
			return false;
		}

		$this->messages[] = "Download completed: " . $filename . " (" . filesize($this->downloadedFile) . " bytes)";

		// Verify hash if provided
		if ($expectedHash && !$this->verifyHash($expectedHash)) {
			return false;
		}

		return true;
	}

	/**
	 * Verify the downloaded file hash
	 *
	 * @param string $expectedHash Expected SHA256 hash
	 * @return bool
	 */
	public function verifyHash($expectedHash) {
		if (!file_exists($this->downloadedFile)) {
			$this->errors[] = "Downloaded file not found for hash verification";
			return false;
		}

		$actualHash = hash_file('sha256', $this->downloadedFile);
		
		if ($actualHash !== $expectedHash) {
			$this->errors[] = "Hash verification failed. Expected: " . $expectedHash . ", Got: " . $actualHash;
			return false;
		}

		$this->messages[] = "Hash verification successful";
		return true;
	}

	/**
	 * Create a backup of the current installation
	 *
	 * @return bool
	 */
	public function createBackup() {
		$backupName = 'update-backup-' . date('Y-m-d-H-i-s');

		$this->messages[] = "Creating backup before update...";

		$backupPath = $this->backup->createBackup($backupName, true);

		if ($backupPath) {
			// Merge backup messages with update messages
			$backupMessages = $this->backup->getMessages(false);
			foreach ($backupMessages as $message) {
				$this->messages[] = $message;
			}
			return true;
		} else {
			// Merge backup errors with update errors
			$backupErrors = $this->backup->getErrors(false);
			foreach ($backupErrors as $error) {
				$this->errors[] = $error;
			}
			return false;
		}
	}

	/**
	 * Extract and install the update
	 *
	 * The archive is extracted to a temporary directory, the package root is located
	 * and checked, and only the files on the install list are written to the site.
	 * Nothing is written when one of the target files is not writable.
	 *
	 * @return bool
	 */
	public function installUpdate() {
		if (!$this->downloadedFile || !file_exists($this->downloadedFile)) {
			$this->errors[] = "Downloaded file not found for installation";
			return false;
		}

		$this->messages[] = "Starting installation process";

		try {
			// Extract to a clean temporary directory first
			$extractDir = $this->tempDir . '/extract';
			if (is_dir($extractDir)) {
				icms_core_Filesystem::deleteRecursive($extractDir);
			}
			if (!is_dir($extractDir) && !mkdir($extractDir, 0755, true)) {
				$this->errors[] = "Failed to create extraction directory: " . $extractDir;
				return false;
			}

			if (!$this->extractArchive($this->downloadedFile, $extractDir)) {
				return false;
			}

			$sourceDir = $this->findPackageRoot($extractDir);
			if ($sourceDir === false) {
				return false;
			}
			$this->messages[] = "Package root: " . $sourceDir;

			$this->fileList = $this->buildFileList($sourceDir, ICMS_ROOT_PATH);
			$this->messages[] = sprintf(
				"%d files to install, %d files to delete, %d files skipped",
				count($this->fileList['install']),
				count($this->fileList['delete']),
				count($this->fileList['skip'])
			);

			if (!$this->checkWritable($this->fileList, ICMS_ROOT_PATH)) {
				return false;
			}

			if (!$this->installFiles($sourceDir, ICMS_ROOT_PATH, $this->fileList)) {
				return false;
			}

			$this->messages[] = "Installation completed successfully";
			return true;

		} catch (Exception $e) {
			$this->errors[] = "Installation failed: " . $e->getMessage();
			return false;
		}
	}

	/**
	 * Extract archive file to destination directory
	 *
	 * Every entry name is checked before anything is extracted, so an archive cannot
	 * write outside the extraction directory.
	 *
	 * @param string $archiveFile Path to archive file
	 * @param string $extractDir Directory to extract to
	 * @return bool
	 */
	private function extractArchive($archiveFile, $extractDir) {
		if (!class_exists('ZipArchive')) {
			$this->errors[] = "Failed to extract archive: the PHP zip extension (ZipArchive) is required.";
			return false;
		}

		$zip = new ZipArchive();
		$result = $zip->open($archiveFile);
		if ($result !== true) {
			$this->errors[] = "Failed to open archive (ZipArchive error " . $result . "). The download may not be a zip file.";
			return false;
		}

		for ($i = 0; $i < $zip->numFiles; $i++) {
			$name = $zip->getNameIndex($i);
			if ($name === false || !self::isSafeRelativePath($name)) {
				$zip->close();
				$this->errors[] = "Archive rejected: unsafe entry name " . ($name === false ? '#' . $i : $name);
				return false;
			}
		}

		if (!$zip->extractTo($extractDir)) {
			$zip->close();
			$this->errors[] = "Failed to extract archive to: " . $extractDir;
			return false;
		}
		$zip->close();

		$this->messages[] = "Archive extracted";
		return true;
	}

	/**
	 * Check that a path from an archive or a file list stays inside its base directory
	 *
	 * @param string $path Relative path
	 * @return bool
	 */
	public static function isSafeRelativePath($path) {
		$path = str_replace('\\', '/', $path);
		if ($path === '' || strpos($path, "\0") !== false) {
			return false;
		}
		// absolute paths and Windows drive letters
		if ($path[0] === '/' || preg_match('#^[a-zA-Z]:#', $path)) {
			return false;
		}
		foreach (explode('/', $path) as $segment) {
			if ($segment === '..') {
				return false;
			}
		}
		return true;
	}

	/**
	 * Locate the web root inside the extracted package
	 *
	 * Release packages built by bin/build-package hold the contents of htdocs/ in the
	 * root of the zip. Archives with a single wrapper directory, with or without an
	 * htdocs/ directory inside it, are accepted too.
	 *
	 * @param string $extractDir Directory the archive was extracted to
	 * @return string|false
	 */
	private function findPackageRoot($extractDir) {
		$candidates = array($extractDir, $extractDir . '/htdocs');

		$entries = array_values(array_diff(scandir($extractDir), array('.', '..')));
		if (count($entries) === 1 && is_dir($extractDir . '/' . $entries[0])) {
			$candidates[] = $extractDir . '/' . $entries[0];
			$candidates[] = $extractDir . '/' . $entries[0] . '/htdocs';
		}

		foreach ($candidates as $candidate) {
			if (!is_file($candidate . '/include/version.php')) {
				continue;
			}
			foreach ($this->requiredFiles as $required) {
				if (!is_file($candidate . '/' . $required)) {
					$this->errors[] = "The package is incomplete: " . $required . " is missing.";
					return false;
				}
			}
			return $candidate;
		}

		$this->errors[] = "The archive is not an ImpressCMS package: include/version.php was not found.";
		return false;
	}

	/**
	 * Check whether the updater must never write or delete a path
	 *
	 * @param string $relativePath Path relative to the web root, with forward slashes
	 * @return bool
	 */
	public function isProtectedPath($relativePath) {
		$path = strtolower($relativePath);
		foreach ($this->protectedPaths as $protected) {
			$protected = strtolower($protected);
			if (substr($protected, -1) === '/') {
				if (strpos($path, $protected) === 0 || $path === rtrim($protected, '/')) {
					return true;
				}
			} elseif ($path === $protected) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Build the list of files to install, delete and skip
	 *
	 * @param string $sourceDir Package root
	 * @param string $destination Site web root
	 * @return array Array with keys 'install', 'delete' and 'skip' (relative paths), and 'skip_reasons'
	 */
	public function buildFileList($sourceDir, $destination) {
		$list = array('install' => array(), 'delete' => array(), 'skip' => array(), 'skip_reasons' => array());
		$sourceDir = rtrim(str_replace('\\', '/', $sourceDir), '/');

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($sourceDir, FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::LEAVES_ONLY
		);

		foreach ($iterator as $item) {
			if (!$item->isFile()) {
				continue;
			}
			$relative = substr(str_replace('\\', '/', $item->getPathname()), strlen($sourceDir) + 1);

			if ($relative === $this->deletedFilesList) {
				continue;
			}
			if ($this->isProtectedPath($relative)) {
				$list['skip'][] = $relative;
				$list['skip_reasons'][$relative] = 'protected';
				continue;
			}
			if (in_array(strtolower($relative), array_map('strtolower', $this->keepExisting), true)
				&& file_exists($destination . '/' . $relative)) {
				$list['skip'][] = $relative;
				$list['skip_reasons'][$relative] = 'kept existing';
				continue;
			}
			$list['install'][] = $relative;
		}

		// Files removed from the core since the previous release (only present in packages built with --from)
		$deletedFile = $sourceDir . '/' . $this->deletedFilesList;
		if (is_file($deletedFile)) {
			foreach (file($deletedFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
				$relative = trim(str_replace('\\', '/', $line));
				if ($relative === '' || !self::isSafeRelativePath($relative) || $this->isProtectedPath($relative)) {
					continue;
				}
				if (is_file($destination . '/' . $relative)) {
					$list['delete'][] = $relative;
				}
			}
		}

		sort($list['install']);
		sort($list['delete']);
		sort($list['skip']);
		return $list;
	}

	/**
	 * Check that every file on the list can be written or deleted, before anything is changed
	 *
	 * @param array $fileList List from buildFileList()
	 * @param string $destination Site web root
	 * @return bool
	 */
	private function checkWritable(array $fileList, $destination) {
		$notWritable = array();

		foreach (array_merge($fileList['install'], $fileList['delete']) as $relative) {
			$target = $destination . '/' . $relative;
			if (file_exists($target)) {
				if (!is_writable($target)) {
					$notWritable[] = $relative;
				}
				continue;
			}
			// new file: the nearest existing parent directory must be writable
			$parent = dirname($target);
			while (!file_exists($parent) && dirname($parent) !== $parent) {
				$parent = dirname($parent);
			}
			if (!is_dir($parent) || !is_writable($parent)) {
				$notWritable[] = $relative;
			}
		}

		// deleting a file needs write access to its directory
		foreach ($fileList['delete'] as $relative) {
			if (!is_writable(dirname($destination . '/' . $relative))) {
				$notWritable[] = $relative;
			}
		}

		if ($notWritable) {
			$notWritable = array_values(array_unique($notWritable));
			$shown = array_slice($notWritable, 0, 20);
			$this->errors[] = sprintf(
				"Update aborted before any file was changed: %d files or directories are not writable by the web server: %s%s",
				count($notWritable),
				implode(', ', $shown),
				count($notWritable) > count($shown) ? ', ...' : ''
			);
			return false;
		}
		return true;
	}

	/**
	 * Copy the files on the install list and delete the files on the delete list
	 *
	 * @param string $sourceDir Package root
	 * @param string $destination Site web root
	 * @param array $fileList List from buildFileList()
	 * @return bool False when one or more files could not be written or deleted
	 */
	private function installFiles($sourceDir, $destination, array $fileList) {
		$failed = array();
		$copied = 0;

		foreach ($fileList['install'] as $relative) {
			$target = $destination . '/' . $relative;
			$dir = dirname($target);
			if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
				$failed[] = $relative . ' (could not create directory)';
				continue;
			}
			if (!copy($sourceDir . '/' . $relative, $target)) {
				$failed[] = $relative;
				continue;
			}
			$copied++;
		}

		$deleted = 0;
		foreach ($fileList['delete'] as $relative) {
			if (unlink($destination . '/' . $relative)) {
				$deleted++;
			} else {
				$failed[] = $relative . ' (could not delete)';
			}
		}

		$this->messages[] = sprintf("%d files installed, %d obsolete files deleted", $copied, $deleted);
		foreach ($fileList['skip'] as $relative) {
			if ($fileList['skip_reasons'][$relative] === 'kept existing') {
				$this->messages[] = "Kept the site's own " . $relative . " (the release version was not installed)";
			}
		}

		if ($failed) {
			$shown = array_slice($failed, 0, 20);
			$this->errors[] = sprintf(
				"%d files could not be updated, the site may be partly updated. Restore the backup or copy these files by hand: %s%s",
				count($failed),
				implode(', ', $shown),
				count($failed) > count($shown) ? ', ...' : ''
			);
			return false;
		}
		return true;
	}

	/**
	 * Get the file list of the last installation attempt
	 *
	 * @return array|null
	 */
	public function getFileList() {
		return $this->fileList;
	}

	/**
	 * Perform complete update process
	 *
	 * @param string $expectedHash Optional hash to verify
	 * @return bool
	 */
	public function performUpdate($expectedHash = null) {
		try {
			// Never fall back to a directory inside the web root
			if (!$this->createDirectories()) {
				return false;
			}

			// Check if update is available
			if (!$this->isUpdateAvailable()) {
				$this->errors[] = "No update available";
				return false;
			}

			// Create backup
			if (!$this->createBackup()) {
				return false;
			}

			// Download update
			if (!$this->downloadUpdate($expectedHash)) {
				return false;
			}

			// Install update
			return $this->installUpdate();
		} finally {
			// Never leave downloaded or extracted files behind, also not when the update failed
			$this->cleanup();
		}
	}

	/**
	 * Clean up temporary files
	 */
	public function cleanup() {
		if ($this->tempDir === false) {
			return;
		}
		if ($this->downloadedFile && file_exists($this->downloadedFile)) {
			icms_core_Filesystem::deleteFile($this->downloadedFile);
		}

		$extractDir = $this->tempDir . '/extract';
		if (is_dir($extractDir)) {
			icms_core_Filesystem::deleteRecursive($extractDir);
		}

		$this->messages[] = "Cleanup completed";
	}

	/**
	 * Get all error messages
	 *
	 * @param bool $asHtml Return as HTML
	 * @return mixed
	 */
	public function getErrors($asHtml = true) {
		if (!$asHtml) {
			return $this->errors;
		}

		$ret = '';
		foreach ($this->errors as $error) {
			$ret .= $error . '<br />';
		}
		return $ret;
	}

	/**
	 * Get all status messages
	 *
	 * @param bool $asHtml Return as HTML
	 * @return mixed
	 */
	public function getMessages($asHtml = true) {
		if (!$asHtml) {
			return $this->messages;
		}

		$ret = '';
		foreach ($this->messages as $message) {
			$ret .= $message . '<br />';
		}
		return $ret;
	}

	/**
	 * Get the backup instance
	 *
	 * @return icms_core_Backup
	 */
	public function getBackup() {
		return $this->backup;
	}

	/**
	 * List available backups
	 *
	 * @return array
	 */
	public function listBackups() {
		return $this->backup->listBackups();
	}

	/**
	 * Delete a backup
	 *
	 * @param string $backupName Backup filename
	 * @return bool
	 */
	public function deleteBackup($backupName) {
		$result = $this->backup->deleteBackup($backupName);

		// Merge backup messages/errors
		$backupMessages = $this->backup->getMessages(false);
		foreach ($backupMessages as $message) {
			$this->messages[] = $message;
		}

		$backupErrors = $this->backup->getErrors(false);
		foreach ($backupErrors as $error) {
			$this->errors[] = $error;
		}

		return $result;
	}

	/**
	 * Check if user has permission to perform updates
	 *
	 * @return bool
	 */
	public static function canUpdate() {
		return is_object(icms::$user) && icms::$user->isAdmin();
	}
}
