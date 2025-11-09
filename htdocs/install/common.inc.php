<?php
/**
 * Installer common include file
 *
 * See the enclosed file license.txt for licensing information.
 * If you did not receive this file, get it at http://www.fsf.org/copyleft/gpl.html
 *
 * @copyright The XOOPS project http://www.xoops.org/
 * @license http://www.fsf.org/copyleft/gpl.html GNU General Public License (GPL)
 * @package installer
 * @since 2.0.14
 * @author Skalpa Keo <skalpa@xoops.org>
 * @version $Id: common.inc.php 12389 2014-01-17 16:58:21Z skenow $
 */

/**
 * If non-empty, only this user can access this installer
 */
define('INSTALL_USER', '');
define('INSTALL_PASSWORD', '');

// options for mainfile.php
$xoopsOption['nocommon'] = true;
define('XOOPS_INSTALL', 1);

/*
 * set the default timezone for date/time functions - for strict PHP 5.3/5.4
 * suppress errors, because we don't care
 * if it's not set, it will be set to UTC, which we would have defaulted, anyway
 */
date_default_timezone_set(@date_default_timezone_get());

if (!defined('PHP_VERSION_ID')) {
	$version = explode('.', PHP_VERSION);

	define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

include_once '../include/version.php';
// including a few functions - core
include_once '../include/functions.php';
// installer common functions
require_once 'include/functions.php';
// template helper functions
require_once 'include/template_helper.php';
include_once './class/IcmsInstallWizard.php';

require_once '../libraries/icms/Autoloader.php';
icms_Autoloader::setup();

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// Set up error handler to catch fatal errors
set_error_handler(function($errno, $errstr, $errfile, $errline) {
	// Don't handle suppressed errors
	if (!(error_reporting() & $errno)) {
		return false;
	}

	// Log the error
	error_log("Installer Error [$errno]: $errstr in $errfile on line $errline");

	// Display error for debugging
	echo '<div style="color: #d32f2f; background-color: #ffebee; padding: 12px; border-radius: 4px; margin: 20px; font-family: monospace;">';
	echo '<strong>Error:</strong> ' . htmlspecialchars($errstr) . '<br>';
	echo '<small>File: ' . htmlspecialchars($errfile) . ' (Line ' . $errline . ')</small>';
	echo '</div>';

	return true;
});

// Set up exception handler
set_exception_handler(function($exception) {
	error_log('Installer Exception: ' . $exception->getMessage());

	echo '<div style="color: #d32f2f; background-color: #ffebee; padding: 12px; border-radius: 4px; margin: 20px; font-family: monospace;">';
	echo '<strong>Exception:</strong> ' . htmlspecialchars($exception->getMessage()) . '<br>';
	echo '<small>File: ' . htmlspecialchars($exception->getFile()) . ' (Line ' . $exception->getLine() . ')</small>';
	echo '</div>';
});

$pageHasHelp = false;
$pageHasForm = false;

$wizard = new IcmsInstallWizard();
if (!$wizard->xoInit()) {
	exit();
}
session_start();

if (!@is_array($_SESSION['settings'])) {
	$_SESSION['settings'] = array();
}