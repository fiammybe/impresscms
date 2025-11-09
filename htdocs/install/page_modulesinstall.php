<?php
/**
 * Installer tables creation page
 *
 * See the enclosed file license.txt for licensing information.
 * If you did not receive this file, get it at http://www.fsf.org/copyleft/gpl.html
 *
 * @copyright    The ImpressCMS project http://www.impresscms.org/
 * @license      http://www.fsf.org/copyleft/gpl.html GNU General Public License (GPL)
 * @package		installer
 * @since        1.0
 * @author		Martijn Hertog (AKA wtravel) <martin@efqconsultancy.com>
 * @version		$Id: page_modulesinstall.php 12389 2014-01-17 16:58:21Z skenow $
 */

// Enable error display for debugging
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once 'common.inc.php';

if (!defined( 'XOOPS_INSTALL' ) )	exit();

$wizard->setPage( 'modulesinstall' );
$pageHasForm = true;
$pageHasHelp = false;

$vars =& $_SESSION['settings'];

include_once "../mainfile.php";
include_once "../include/common.php";
include_once "../include/cp_functions.php";
include_once './class/dbmanager.php';
include "modulesadmin.php";
$dbm = new db_manager();

if (!$dbm->isConnectable()) {
	$wizard->redirectToPage( '-3' );
	exit();
}
$process = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$process = 'install';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	// If there's nothing to do: switch to next page
	if (empty( $process )) {
		$wizard->redirectToPage( '+1' );
		exit();
	}

	// Start output buffering to catch any errors
	ob_start();

	try {
		if ($_POST['mod'] == 1) {
			/**
			 * Automatically updating the system module before installing the selected modules
			 * @since 1.3
			 */
			$content = '';

			// Validate system update file exists
			if (!file_exists(ICMS_ROOT_PATH . '/modules/system/include/update.php')) {
				throw new Exception('System module update file not found: ' . ICMS_ROOT_PATH . '/modules/system/include/update.php');
			}

			include_once ICMS_ROOT_PATH . '/modules/system/include/update.php';
			$module_handler = icms::handler('icms_module');
			if (!$module_handler) {
				throw new Exception('Failed to get module handler');
			}

			$system_moduleObj = $module_handler->getByDirname('system');
			if (!$system_moduleObj) {
				throw new Exception('System module not found in database');
			}

			// Update system module
			$updateResult = xoops_module_update_system($system_moduleObj);
			if ($updateResult === false) {
				throw new Exception('Failed to update system module');
			}

			$install_mods = isset($_POST['install_mods']) ? $_POST['install_mods'] : '';
			$anon_accessible_mods = isset($_POST['anon_accessible_mods']) ? $_POST['anon_accessible_mods'] : '';

			if (isset($_POST['install_mods']) && is_array($install_mods) && count($install_mods) > 0) {
				for ($i = 0; $i <= count($install_mods)-1; $i++) {
					$moduleName = trim($install_mods[$i]);
					if (empty($moduleName)) {
						continue;
					}

					try {
						$installResult = xoops_module_install($moduleName);
						if ($installResult === false) {
							throw new Exception("Failed to install module: $moduleName");
						}
						$content .= $installResult;
						impresscms_get_adminmenu();
					} catch (Exception $e) {
						error_log('Module installation error for ' . $moduleName . ': ' . $e->getMessage());
						$content .= '<div style="color: #d32f2f; background-color: #ffebee; padding: 12px; border-radius: 4px; margin: 10px 0;">';
						$content .= '<strong>Error installing ' . htmlspecialchars($moduleName) . ':</strong> ' . htmlspecialchars($e->getMessage());
						$content .= '</div>';
					}
				}
			} else {
				$content .= _INSTALL_NO_PLUS_MOD;
			}

			// Install protector module by default if found
			if (file_exists(ICMS_ROOT_PATH . '/modules/protector/icms_version.php')) {
				try {
					$protectorResult = xoops_module_install('protector');
					if ($protectorResult === false) {
						throw new Exception('Failed to install protector module');
					}
					$content .= $protectorResult;
				} catch (Exception $e) {
					error_log('Protector module installation error: ' . $e->getMessage());
					$content .= '<div style="color: #d32f2f; background-color: #ffebee; padding: 12px; border-radius: 4px; margin: 10px 0;">';
					$content .= '<strong>Error installing protector module:</strong> ' . htmlspecialchars($e->getMessage());
					$content .= '</div>';
				}
			}

			$tables = array();
			$content .= "<div style='height:auto;max-height:400px;overflow:auto;'>".$dbm->report()."</div>";

			// Clear output buffer
			ob_end_clean();

			// Render success page with report
			$msg = 'Module installation completed.';
			$pageHasForm = false;

			renderInstallerLayout($wizard, [
				'message' => $msg,
				'content' => $content,
				'processInstall' => false,
			], $pageHasForm);

			// Auto-redirect to next page after a short delay
			echo '<script>setTimeout(function() { window.location.href = "' . $wizard->pageURI('+1') . '"; }, 3000);</script>';

		} else {
			ob_end_clean();
			$wizard->redirectToPage( '+1' );
			exit();
		}

	} catch (Exception $e) {
		// Clear output buffer
		ob_end_clean();

		// Log the error
		error_log('Installer modulesinstall error: ' . $e->getMessage());

		// Display error message
		$errorMsg = 'Error during module installation: ' . htmlspecialchars($e->getMessage());

		renderInstallerLayout($wizard, [
			'message' => $errorMsg,
			'error' => true,
		], false);
	}
} else {
	// Get available modules
	$availableModules = [];
	$langarr = icms_module_Handler::getAvailable();
	foreach ($langarr as $lang) {
		if ($lang === 'system' || $lang === 'protector') {
			continue;
		}
		$availableModules[] = [
			'name' => $lang,
			'icon' => "../modules/$lang/images/icon_small.png",
		];
	}

	// Render the full layout with page variables
	renderInstallerLayout($wizard, [
		'selectModsIntro' => _INSTALL_SELECT_MODS_INTRO,
		'selectModulesLabel' => _INSTALL_SELECT_MODULES,
		'availableModules' => $availableModules,
	], true);
}