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

		$this->tempDir = ICMS_CACHE_PATH . '/updates';

		// Ensure directories exist
		$this->createDirectories();
	}

	/**
	 * Create necessary directories
	 *
	 * @return bool
	 */
	private function createDirectories() {
		if (!is_dir($this->tempDir)) {
			if (!icms_core_Filesystem::mkdir($this->tempDir, 0755, '')) {
				$this->errors[] = "Failed to create temporary directory: " . $this->tempDir;
				return false;
			}
		}

		return true;
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
	 * @return bool
	 */
	public function installUpdate() {
		if (!file_exists($this->downloadedFile)) {
			$this->errors[] = "Downloaded file not found for installation";
			return false;
		}

		$this->messages[] = "Starting installation process";

		try {
			// Extract to temporary directory first
			$extractDir = $this->tempDir . '/extract';
			if (!is_dir($extractDir)) {
				icms_core_Filesystem::mkdir($extractDir, 0755, '');
			}

			// Try to extract using ZipArchive first, fallback to alternative methods
			if (!$this->extractArchive($this->downloadedFile, $extractDir)) {
				return false;
			}

			// Find the extracted directory (usually has a version name)
			$extractedDirs = glob($extractDir . '/*', GLOB_ONLYDIR);
			if (empty($extractedDirs)) {
				$this->errors[] = "No extracted directory found";
				return false;
			}

			$sourceDir = $extractedDirs[0];
			$this->messages[] = "Extracted to: " . $sourceDir;

			// Copy files to ImpressCMS root
			if (!$this->copyFiles($sourceDir, ICMS_ROOT_PATH)) {
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
	 * @param string $archiveFile Path to archive file
	 * @param string $extractDir Directory to extract to
	 * @return bool
	 */
	private function extractArchive($archiveFile, $extractDir) {
		// Try ZipArchive first if available
		if (class_exists('ZipArchive')) {
			$zip = new ZipArchive();
			$result = $zip->open($archiveFile);

			if ($result === TRUE) {
				if ($zip->extractTo($extractDir)) {
					$zip->close();
					$this->messages[] = "Archive extracted using ZipArchive";
					return true;
				}
				$zip->close();
			}
		}

		// Fallback: Try to use system unzip command if available
		if (function_exists('exec')) {
			$command = "unzip -q " . escapeshellarg($archiveFile) . " -d " . escapeshellarg($extractDir);
			$output = array();
			$returnCode = 0;
			exec($command, $output, $returnCode);

			if ($returnCode === 0) {
				$this->messages[] = "Archive extracted using system unzip command";
				return true;
			}
		}

		$this->errors[] = "Failed to extract archive. ZipArchive extension and system unzip command are not available.";
		return false;
	}

	/**
	 * Copy files from source to destination
	 *
	 * @param string $source Source directory
	 * @param string $destination Destination directory
	 * @return bool
	 */
	private function copyFiles($source, $destination) {
		try {
			// Use icms_core_Filesystem::copyRecursive for consistency
			if (icms_core_Filesystem::copyRecursive($source, $destination)) {
				$this->messages[] = "Files copied successfully from " . basename($source) . " to " . basename($destination);
				return true;
			} else {
				$this->errors[] = "Failed to copy files from " . $source . " to " . $destination;
				return false;
			}

		} catch (Exception $e) {
			$this->errors[] = "File copy failed: " . $e->getMessage();
			return false;
		}
	}

	/**
	 * Perform complete update process
	 *
	 * @param string $expectedHash Optional hash to verify
	 * @return bool
	 */
	public function performUpdate($expectedHash = null) {
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
		if (!$this->installUpdate()) {
			return false;
		}

		// Clean up temporary files
		$this->cleanup();

		return true;
	}

	/**
	 * Clean up temporary files
	 */
	public function cleanup() {
		if (file_exists($this->downloadedFile)) {
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
