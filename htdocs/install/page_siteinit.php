<?php
/**
 * Installer initial site configuration page
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
 * @version		$Id: page_siteinit.php 11750 2012-06-28 15:31:34Z m0nty $
 */
/**
 *
 */
require_once 'common.inc.php';
if (!defined( 'XOOPS_INSTALL' ) )	exit();

$wizard->setPage( 'siteinit' );
$pageHasForm = true;
$pageHasHelp = false;

$vars =& $_SESSION['siteconfig'];

$error =& $_SESSION['error'];


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$vars['adminname'] = $_POST['adminname'];
	$vars['adminlogin_name'] = $_POST['adminlogin_name'];
	$vars['adminmail'] = $_POST['adminmail'];
	$vars['adminpass'] = $_POST['adminpass'];
	$vars['adminpass2'] = $_POST['adminpass2'];
	$error = '';

	if (!preg_match( "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+([\.][a-z0-9-]+)+$/i", $vars['adminmail'] )) {
		$error = ERR_INVALID_EMAIL;
	} elseif (@empty( $vars['adminlogin_name'] ) || @empty( $vars['adminname'] )  || @empty( $vars['adminlogin_name'] ) || @empty( $vars['adminpass'] ) || @empty( $vars['adminmail'])) {
		$error = ERR_REQUIRED;
	} elseif ($vars['adminpass'] != $vars['adminpass2']) {
		$error = ERR_PASSWORD_MATCH;
	}
	if ($error) {
		$wizard->redirectToPage( '+0' );
		return 200;
	} else {
		$wizard->redirectToPage( '+1' );
		return 302;
	}
}

// Render the full layout with page variables
renderInstallerLayout($wizard, [
	'error' => isset($error) ? $error : '',
	'adminAccountLegend' => LEGEND_ADMIN_ACCOUNT,
	'adminDisplayLabel' => ADMIN_DISPLAY_LABEL,
	'adminDisplayValue' => isset($vars['adminname']) ? htmlspecialchars($vars['adminname'], ENT_QUOTES) : '',
	'adminLoginLabel' => ADMIN_LOGIN_LABEL,
	'adminLoginValue' => isset($vars['adminlogin_name']) ? htmlspecialchars($vars['adminlogin_name'], ENT_QUOTES) : '',
	'adminEmailLabel' => ADMIN_EMAIL_LABEL,
	'adminEmailValue' => isset($vars['adminmail']) ? htmlspecialchars($vars['adminmail'], ENT_QUOTES) : '',
	'adminPassLabel' => ADMIN_PASS_LABEL,
	'adminConfirmPassLabel' => ADMIN_CONFIRMPASS_LABEL,
	'shortPassLabel' => _CORE_PASSLEVEL1,
	'badPassLabel' => _CORE_PASSLEVEL2,
	'goodPassLabel' => _CORE_PASSLEVEL3,
	'strongPassLabel' => _CORE_PASSLEVEL4,
], true);

// Clear error for next page
$error = '';
