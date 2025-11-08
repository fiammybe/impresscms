<?php
/**
 * Installer configuration check page
 *
 * See the enclosed file license.txt for licensing information.
 * If you did not receive this file, get it at http://www.fsf.org/copyleft/gpl.html
 *
 * @copyright   The XOOPS project http://www.xoops.org/
 * @license     http://www.fsf.org/copyleft/gpl.html GNU General Public License (GPL)
 * @package		installer
 * @since       2.3.0
 * @author		Haruki Setoyama  <haruki@planewave.org>
 * @author 		Kazumi Ono <webmaster@myweb.ne.jp>
 * @author		Skalpa Keo <skalpa@xoops.org>
 * @author		Taiwen Jiang <phppp@users.sourceforge.net>
 * @author		David Janssens <david.j@impresscms.org>
 */

/**
 *
 */
require_once 'common.inc.php';
if (!defined( 'XOOPS_INSTALL' ) )	exit();
$requirements_array = array();

$wizard->setPage( 'modcheck' );
$pageHasForm = false;

$diagsOK = false;

function xoDiag( $status = -1, $str = '') {
	if ($status == -1) {
		$GLOBALS['error'] = true;
	}
	$classes = array( -1 => 'error', 0 => 'warning', 1 => 'success' );
	$strings = array( -1 => FAILED, 0 => WARNING, 1 => SUCCESS );
	if (empty($str)) {
		$str = $strings[$status];
	}
	return '<td class="' . $classes[$status] . '">' . $str . '</td>';
}
function xoDiagBoolSetting( $name, $wanted = false, $severe = false) {
	$setting = strtolower( ini_get( $name ) );
	$setting = ( empty( $setting ) || $setting == 'off' || $setting == 'false' ) ? false : true;
	if ($setting == $wanted) {
		return xoDiag( 1, $setting ? 'ON' : 'OFF' );
	} else {
		return xoDiag( $severe ? -1 : 0, $setting ? 'ON' : 'OFF' );
	}
}

function xoDiagIfWritable( $path) {
	$path = "../" . $path;
	$error = true;
	if (!is_dir( $path )) {
		if (file_exists( $path )) {
			@chmod( $path, 0666 );
			$error = !is_writeable( $path );
		}
	} else {
		@chmod( $path, 0777 );
		$error = !is_writeable( $path );
	}
	return xoDiag( $error ? -1 : 1, $error ? 'Not writable' : 'Writable' );
}

function imCheckRequirements()
{
	$requirement['server_api']['description']=PHP_SAPI;
	$requirement['server_api']['result']=php_sapi_name();
	$requirement['server_api']['status']=true;

	$requirement['php_version']['description']=_PHP_VERSION;
	if (version_compare( phpversion(), '7.0', '>=')) {
		$requirement['php_version']['status']=1;
	} else {
		$requirement['php_version']['status']=0;
	}
	$requirement['php_version']['result']=phpversion();

	$requirement['mysql']['description']="MySQL Handler";
	$requirement['mysql']['result']=in_array("mysql",PDO::getAvailableDrivers(),TRUE) ? SUCCESS : FAILED;
	$requirement['mysql']['status']=in_array("mysql",PDO::getAvailableDrivers(),TRUE) ? true : false;

	$requirement['session']['description']="Session Extension";
	$requirement['session']['result']=extension_loaded( 'session' ) ? SUCCESS : FAILED;
	$requirement['session']['status']=extension_loaded( 'session' ) ? true : false;

	$requirement['pcre']['description']="PCRE Extension";
	$requirement['pcre']['result']=extension_loaded( 'PCRE' ) ? SUCCESS : FAILED;
	$requirement['pcre']['status']=extension_loaded( 'PCRE' ) ? true : false;

	$requirement['curl']['description']="CURL Extension";
	$requirement['curl']['result']=extension_loaded( 'curl' ) ? SUCCESS : FAILED;
	$requirement['curl']['status']=extension_loaded( 'curl' ) ? true : false;

	$requirement['file_upload']['description']="File uploads";
	$requirement['file_upload']['result']=xoDiagBoolSetting( 'file_uploads', true ) ? SUCCESS : FAILED;
	$requirement['file_upload']['status']=xoDiagBoolSetting( 'file_uploads', true ) ? true : false;

	$requirement['gd']['description']="GD Extension";
	$requirement['gd']['result']=extension_loaded( 'GD' ) ? SUCCESS : FAILED;
	$requirement['gd']['status']=extension_loaded( 'GD' ) ? true : false;


	return $requirement;
}

$requirements_array = imCheckRequirements();

// Prepare character encoding extensions
$charEncodingExtensions = '';
$charEncodingExts = [];
if (extension_loaded('iconv')) {
	$charEncodingExts[] = 'Iconv';
}
if (extension_loaded('mb_string')) {
	$charEncodingExts[] = 'MBString';
}
if (!empty($charEncodingExts)) {
	$charEncodingExtensions = implode(',', $charEncodingExts);
}

// Prepare XML extensions
$xmlExtensions = '';
$xmlExts = [];
if (extension_loaded('xml')) {
	$xmlExts[] = 'XML';
}
if (!empty($xmlExts)) {
	$xmlExtensions = implode(',', $xmlExts);
}

// Render the full layout with page variables
renderInstallerLayout($wizard, [
	'requirements' => $requirements_array,
	'requirementsLabel' => REQUIREMENTS,
	'recommendedExtensionsLabel' => RECOMMENDED_EXTENSIONS,
	'recommendedExtensionsMsg' => RECOMMENDED_EXTENSIONS_MSG,
	'charEncodingLabel' => sprintf(PHP_EXTENSION, CHAR_ENCODING),
	'charEncodingExtensions' => $charEncodingExtensions,
	'xmlParsingLabel' => sprintf(PHP_EXTENSION, XML_PARSING),
	'xmlExtensions' => $xmlExtensions,
	'successLabel' => SUCCESS,
	'failedLabel' => FAILED,
	'warningLabel' => WARNING,
	'noneLabel' => NONE,
], false);
