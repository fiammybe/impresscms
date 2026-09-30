<?php
/**
 * ImpressCMS Backup Manager
 *
 * Backup management interface for ImpressCMS
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package		Administration
 * @since		1.4
 * @author		ImpressCMS Development Team
 */

include_once '../../../../mainfile.php';
include_once ICMS_ROOT_PATH . '/include/cp_functions.php';
include_once dirname(__FILE__) . '/class/BackupInfo.php';

// Security check
if (!is_object(icms::$user) || !is_object(icms::$module) || !icms::$user->isAdmin(icms::$module->getVar('mid'))) {
	exit("Access Denied");
}

/**
 * Escape a value for HTML output
 *
 * @param string $text
 * @return string
 */
function backup_h($text) {
	return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}

/**
 * Join backup class messages into escaped HTML
 *
 * @param array $items
 * @return string
 */
function backup_join($items) {
	return implode('<br />', array_map('backup_h', $items));
}

$backup = new icms_core_Backup();
$message = '';
$error = '';
$infoObj = null;
$backupContents = null;

$isPost = ($_SERVER['REQUEST_METHOD'] === 'POST');
$action = $isPost
	? (string)filter_input(INPUT_POST, 'action', FILTER_UNSAFE_RAW)
	: (string)filter_input(INPUT_GET, 'action', FILTER_UNSAFE_RAW);

// State-changing actions require POST and a valid security token
$stateChanging = array('create', 'checksum', 'delete', 'restore');
if (in_array($action, $stateChanging, true) && (!$isPost || !icms::$security->check())) {
	$error = "Invalid request: security token check failed or POST required.";
	$action = '';
}

switch ($action) {
	case 'create':
		$rawName = trim((string)filter_input(INPUT_POST, 'backup_name', FILTER_UNSAFE_RAW));
		$backupName = $rawName !== '' ? $backup->sanitizeName($rawName) : 'manual-backup-' . date('Y-m-d-H-i-s');
		$includeUploads = (bool)filter_input(INPUT_POST, 'include_uploads', FILTER_VALIDATE_BOOLEAN);

		if ($backupName === false) {
			$error = "Invalid backup name. Use only letters, numbers, dots, dashes and underscores.";
		} elseif ($backupPath = $backup->createBackup($backupName, true, $includeUploads)) {
			$message = "Backup created successfully: " . basename($backupPath);
		} else {
			$error = "Failed to create backup: " . backup_join($backup->getErrors(false));
		}
		break;

	case 'checksum':
		if ($backup->generateChecksum()) {
			$message = "Checksum file generated successfully";
		} else {
			$error = "Failed to generate checksum: " . backup_join($backup->getErrors(false));
		}
		break;

	case 'delete':
		$filename = $backup->sanitizeName((string)filter_input(INPUT_POST, 'backup_file', FILTER_UNSAFE_RAW));
		if ($filename !== false && $backup->deleteBackup($filename)) {
			$message = "Backup deleted successfully: " . $filename . ".zip";
		} else {
			$error = "Failed to delete backup: " . backup_join($backup->getErrors(false));
		}
		break;

	case 'restore':
		$filename = $backup->sanitizeName((string)filter_input(INPUT_POST, 'backup_file', FILTER_UNSAFE_RAW));
		$createPreBackup = (bool)filter_input(INPUT_POST, 'create_pre_backup', FILTER_VALIDATE_BOOLEAN);

		if ($filename !== false && $backup->restoreBackup($filename, $createPreBackup)) {
			$message = "Backup restored successfully: " . $filename . ".zip";
		} else {
			$error = "Failed to restore backup: " . backup_join($backup->getErrors(false));
		}
		break;

	case 'info':
		$filename = $backup->sanitizeName((string)filter_input(INPUT_GET, 'file', FILTER_UNSAFE_RAW));
		if ($filename !== false) {
			$backupInfo = $backup->getBackupInfo($filename);
			if ($backupInfo) {
				$infoObj = new SystemBackupInfo($backupInfo);
				$backupContents = $backup->listBackupContents($filename, 50); // Show first 50 files
			} else {
				$error = backup_join($backup->getErrors(false));
			}
		} else {
			$error = "Invalid backup name";
		}
		break;
}

// Get list of backups
$backups = $backup->listBackups();
$tokenHtml = icms::$security->getTokenHTML();

icms_cp_header();
?>

<div class="CPbigTitle" style="background-image: url(<?php echo ICMS_URL; ?>/modules/system/admin/backup/images/backup_big.png)">Backup Manager</div><br />

<?php if ($message): ?>
<div class="successMsg"><?php echo backup_h($message); ?></div>
<?php endif; ?>

<?php if ($error): ?>
<div class="errorMsg"><?php echo $error; ?></div>
<?php endif; ?>

<div class="head" style="padding: 2px; margin-bottom: 5px;">
	<strong>Create New Backup</strong>
</div>

<div class="even">
	<form method="post" action="">
		<input type="hidden" name="action" value="create" />
		<?php echo $tokenHtml; ?>
		<div style="margin-bottom: 10px;">
			<label for="backup_name">Backup Name (optional):</label><br />
			<input type="text" name="backup_name" id="backup_name" placeholder="Leave empty for auto-generated name" style="width: 300px;" />
		</div>

		<div style="margin-bottom: 15px;">
			<label>
				<input type="checkbox" name="include_uploads" value="1" id="include_uploads" />
				Include uploads directory in backup
			</label>
			<div style="margin-left: 20px; margin-top: 5px; color: #666; font-size: 12px;">
				<strong>Note:</strong> Including uploads may significantly increase backup size and creation time.<br />
				Uploads typically contain user-uploaded files like images, documents, and media files.
			</div>
		</div>

		<input type="submit" value="Create Backup" class="formButton" onclick="return confirmBackupCreation();" />
	</form>

	<form method="post" action="" style="margin-top: 10px;">
		<input type="hidden" name="action" value="checksum" />
		<?php echo $tokenHtml; ?>
		<input type="submit" value="Generate Checksum File" class="formButton" onclick="return confirm('Generate a checksum file for integrity verification?');" />
	</form>
</div>

<div class="head" style="padding: 2px; margin-bottom: 5px; margin-top: 20px;">
	<strong>Available Backups (<?php echo count($backups); ?>)</strong>
</div>

<div class="odd">
	<?php if (empty($backups)): ?>
		<p>No backups found.</p>
	<?php else: ?>
		<table style="width: 100%; border-collapse: collapse;">
			<thead>
				<tr style="background-color: #f0f0f0;">
					<th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Backup Name</th>
					<th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Size</th>
					<th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Created</th>
					<th style="padding: 8px; border: 1px solid #ddd; text-align: center;">Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($backups as $item): ?>
				<tr>
					<td style="padding: 8px; border: 1px solid #ddd;"><?php echo backup_h($item['name']); ?></td>
					<td style="padding: 8px; border: 1px solid #ddd;"><?php echo backup_h($item['size_formatted']); ?></td>
					<td style="padding: 8px; border: 1px solid #ddd;"><?php echo backup_h($item['created_formatted']); ?></td>
					<td style="padding: 8px; border: 1px solid #ddd; text-align: center;">
						<a href="?action=info&amp;file=<?php echo urlencode($item['name']); ?>" style="color: blue;">Info</a> |
						<a href="javascript:void(0);" data-name="<?php echo backup_h($item['name']); ?>" onclick="showRestoreForm(this.getAttribute('data-name'));" style="color: green;">Restore</a> |
						<form method="post" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this backup?');">
							<input type="hidden" name="action" value="delete" />
							<input type="hidden" name="backup_file" value="<?php echo backup_h($item['name']); ?>" />
							<?php echo icms::$security->getTokenHTML(); ?>
							<input type="submit" value="Delete" style="color: red; background: none; border: none; cursor: pointer; text-decoration: underline; padding: 0;" />
						</form>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>

<div class="head" style="padding: 2px; margin-bottom: 5px; margin-top: 20px;">
	<strong>Backup Information</strong>
</div>

<div class="even">
	<p><strong>Backup Directory:</strong> <?php echo backup_h($backup->getBackupDir()); ?> (stored outside the web root)</p>
	<p><strong>Source Directory:</strong> <?php echo backup_h($backup->getSourceDir()); ?></p>
	<p><strong>Backup Format:</strong> ZIP (compressed)</p>
	<p><strong>Default Excluded Directories:</strong> cache, templates_c, backups</p>
	<p><strong>Uploads Directory:</strong> Excluded by default (can be included via checkbox)</p>
	<p><strong>Excluded Files:</strong> *.log, *.tmp, .DS_Store, Thumbs.db</p>
	<p><strong>Maximum File Size:</strong> 50MB</p>
</div>

<!-- Restore Form (Hidden by default) -->
<div id="restoreForm" style="display: none; margin-top: 20px;">
	<div class="head" style="padding: 2px; margin-bottom: 5px;">
		<strong>Restore Backup</strong>
	</div>
	<div class="even">
		<form method="post" action="" onsubmit="return confirmRestore();">
			<input type="hidden" name="action" value="restore" />
			<input type="hidden" name="backup_file" id="restore_backup_file" value="" />
			<?php echo $tokenHtml; ?>

			<p><strong>Selected Backup:</strong> <span id="restore_backup_name"></span></p>

			<div style="margin: 10px 0;">
				<label>
					<input type="checkbox" name="create_pre_backup" value="1" checked="checked" />
					Create a backup before restoring (recommended)
				</label>
			</div>

			<div style="margin: 10px 0;">
				<strong style="color: red;">Warning:</strong> This will overwrite your current installation files.
				Make sure you have a recent backup before proceeding.
			</div>

			<input type="submit" value="Restore Backup" class="formButton" style="background-color: #ff6600;" />
			<input type="button" value="Cancel" class="formButton" onclick="hideRestoreForm();" />
		</form>
	</div>
</div>

<?php if ($infoObj): ?>
<!-- Backup Information Display -->
<div class="head" style="padding: 2px; margin-bottom: 5px; margin-top: 20px;">
	<strong>Backup Information: <?php echo $infoObj->getVar('name'); ?></strong>
</div>

<div class="odd">
	<?php
	$singleView = new icms_ipf_view_Single($infoObj, false, array(), false);
	foreach (array('name', 'size', 'created', 'files', 'valid_zip') as $key) {
		$singleView->addRow(new icms_ipf_view_Row($key));
	}
	$singleView->render();
	?>

	<?php if ($backupContents): ?>
	<h4>Contents (showing first <?php echo (int)$backupContents['showing']; ?> of <?php echo number_format($backupContents['total_files']); ?> files):</h4>
	<div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background-color: #f9f9f9;">
		<?php foreach ($backupContents['files'] as $file): ?>
			<?php if (!$file['is_directory']): ?>
			<div style="margin: 2px 0; font-family: monospace; font-size: 12px;">
				<?php echo backup_h($file['name']); ?>
				<span style="color: #666;">(<?php echo backup_h($file['size_formatted']); ?>)</span>
			</div>
			<?php endif; ?>
		<?php endforeach; ?>
		<?php if ($backupContents['truncated']): ?>
		<div style="margin-top: 10px; font-style: italic; color: #666;">
			... and <?php echo number_format($backupContents['total_files'] - $backupContents['showing']); ?> more files
		</div>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<p style="margin-top: 15px;">
		<a href="?" class="formButton">← Back to Backup List</a>
	</p>
</div>
<?php endif; ?>

<script type="text/javascript">
function showRestoreForm(backupName) {
	document.getElementById('restore_backup_file').value = backupName;
	document.getElementById('restore_backup_name').textContent = backupName;
	document.getElementById('restoreForm').style.display = 'block';
	document.getElementById('restoreForm').scrollIntoView();
}

function hideRestoreForm() {
	document.getElementById('restoreForm').style.display = 'none';
}

function confirmBackupCreation() {
	var includeUploads = document.getElementById('include_uploads').checked;

	var message = 'Create a new backup?\n\n';

	if (includeUploads) {
		message += 'Including uploads directory: YES\n';
		message += 'Note: This may significantly increase backup size and creation time.\n\n';
	} else {
		message += 'Including uploads directory: NO\n';
		message += 'Note: User-uploaded files will not be included in this backup.\n\n';
	}

	message += 'This operation may take several minutes to complete.';

	return confirm(message);
}

function confirmRestore() {
	var backupName = document.getElementById('restore_backup_name').textContent;
	var createPreBackup = document.querySelector('input[name="create_pre_backup"]').checked;

	var message = 'Are you sure you want to restore from backup "' + backupName + '"?\n\n';
	message += 'This will overwrite your current installation files.\n';

	if (createPreBackup) {
		message += '\nA backup of your current installation will be created first.';
	} else {
		message += '\nWARNING: No backup will be created before restoring!';
	}

	message += '\n\nThis operation may take several minutes to complete.';

	return confirm(message);
}
</script>

<?php
icms_cp_footer();
