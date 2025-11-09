<?php
/**
 * Installer DB data insertion page
 *
 * See the enclosed file license.txt for licensing information.
 * If you did not receive this file, get it at http://www.fsf.org/copyleft/gpl.html
 *
 * @copyright    The XOOPS project http://www.xoops.org/
 * @license      http://www.fsf.org/copyleft/gpl.html GNU General Public License (GPL)
 * @package		installer
 * @since        Xoops 2.3.0
 * @author		Haruki Setoyama  <haruki@planewave.org>
 * @author 		Kazumi Ono <webmaster@myweb.ne.jp>
 * @author		Skalpa Keo <skalpa@xoops.org>
 * @version		$Id: page_tablesfill.php 12426 2014-02-24 16:19:49Z fiammy $
 */

// Enable error display for debugging
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once 'common.inc.php';
if (!defined( 'XOOPS_INSTALL' ) )	exit();

$wizard->setPage( 'tablesfill' );
$pageHasForm = false;
$pageHasHelp = false;

$vars =& $_SESSION['settings'];

include_once "../mainfile.php";
include_once './class/dbmanager.php';
$dbm = new db_manager();

if (!$dbm->isConnectable()) {
	$wizard->redirectToPage( 'dbsettings' );
	exit();
}
$res = $dbm->query( "SELECT COUNT(*) FROM " . $dbm->db->prefix( "users" ) );
if (!$res) {
	$wizard->redirectToPage( 'dbsettings' );
	exit();
}
list ( $count ) = $dbm->db->fetchRow( $res );
$process = $count ? '' : 'insert';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!$process) {
		$wizard->redirectToPage( '+0' );
		exit();
	}

	// Start output buffering to catch any errors
	ob_start();

	try {
		include_once './makedata.php';
		$cm = 'dummy';

		$wizard->loadLangFile( 'install2' );

		extract( $_SESSION['siteconfig'], EXTR_SKIP );
		$language = $wizard->language;

		if (substr(XOOPS_DB_TYPE, 0, 4) == 'pdo.') {
			$driver = substr(XOOPS_DB_TYPE, 4);
		} else {
			$driver = XOOPS_DB_TYPE;
		}

		// Execute SQL data file
		if (!file_exists('./sql/'. $driver .'.data.sql')) {
			throw new Exception('Data SQL file not found: ./sql/'. $driver .'.data.sql');
		}
		$result = $dbm->queryFromFile('./sql/'. $driver .'.data.sql');
		if ($result === false) {
			throw new Exception('Failed to execute data SQL file');
		}

		// Execute language data file
		$langDataFile = './language/' . $language . '/'. $driver . '.lang.data.sql';
		if (file_exists($langDataFile)) {
			$result = $dbm->queryFromFile($langDataFile);
			if ($result === false) {
				throw new Exception('Failed to execute language data SQL file');
			}
		}

		// Create groups
		$group = make_groups( $dbm );
		if (!$group) {
			throw new Exception('Failed to create user groups');
		}

		// Insert data
		$result = make_data( $dbm, $cm, $adminname, $adminlogin_name, $adminpass, $adminmail, $language, $group );
		if (!$result) {
			throw new Exception('Failed to insert data');
		}

		// Get report
		$content = $dbm->report();

		// Clear output buffer
		ob_end_clean();

		// Render success page with report
		$msg = 'Data insertion completed successfully.';
		$pageHasForm = false;

		renderInstallerLayout($wizard, [
			'message' => $msg,
			'content' => $content,
			'processInsert' => false,
		], $pageHasForm);

		// Redirect to next page after a short delay
		echo '<script>setTimeout(function() { window.location.href = "' . $wizard->pageURI('+1') . '"; }, 2000);</script>';

	} catch (Exception $e) {
		// Clear output buffer
		ob_end_clean();

		// Log the error
		error_log('Installer tablesfill error: ' . $e->getMessage());

		// Display error message
		$errorMsg = 'Error during data insertion: ' . htmlspecialchars($e->getMessage());

		renderInstallerLayout($wizard, [
			'message' => $errorMsg,
			'error' => true,
		], false);
	}
} else {
	$msg = $process ? READY_INSERT_DATA : DATA_ALREADY_INSERTED;
	$pageHasForm = $process ? true : false;

	// Render the full layout with page variables
	renderInstallerLayout($wizard, [
		'message' => $msg,
		'processInsert' => !empty($process),
	], $pageHasForm);
}