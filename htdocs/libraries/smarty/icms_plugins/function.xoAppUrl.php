<?php
/**
 * xoAppUrl Smarty function plug-in for Smarty 5
 *
 * See the enclosed file LICENSE for licensing information.
 * If you did not receive this file, get it at http://www.fsf.org/copyleft/gpl.html
 *
 * @copyright   The XOOPS project http://www.xoops.org/
 * @license     http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author		Skalpa Keo <skalpa@xoops.org>
 * @package		xos_opal
 * @subpackage	xos_opal_Smarty
 * @since       2.0.14
 * @version		$Id: compiler.xoAppUrl.php 694 2006-09-04 11:33:22Z skalpa $
 *
 * Updated for Smarty 5 compatibility - converted to function plugin
 */

/**
 * Inserts the URL of an application page
 *
 * This plug-in allows you to generate a module location URL. It uses any URL rewriting
 * mechanism and rules you'll have configured for the system.
 *
 * Examples:
 * {xoAppUrl modules/something/yourpage.php}
 * {xoAppUrl mod_xoops_Identification#logout}
 * {xoAppUrl "modules/something/yourpage.php?order=`$sortby`"}
 *
 * @param array $params Parameters passed to the function
 * @param object $smarty Smarty template object
 * @return string The generated URL
 */
function smarty_function_xoAppUrl($params, &$smarty) {
	// Get the URL - it's passed as the default unnamed parameter or as 'url'
	$url = '';
	foreach ($params as $key => $value) {
		if (is_int($key) || $key === 'url' || $key === '0') {
			$url = $value;
			unset($params[$key]);
			break;
		}
	}
	
	if (empty($url)) {
		return '';
	}
	
	// Handle . for current URL
	if ($url == '.') {
		return htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8');
	}
	
	// Handle /something URLs
	if (substr($url, 0, 1) == '/') {
		$url = 'www' . $url;
	}
	
	// Build URL with any additional parameters
	if (!empty($params)) {
		$url = icms::buildUrl($url, $params);
	}
	
	// Generate the final URL
	$finalUrl = icms::path($url, true);
	
	return htmlspecialchars($finalUrl, ENT_QUOTES, 'UTF-8');
}
