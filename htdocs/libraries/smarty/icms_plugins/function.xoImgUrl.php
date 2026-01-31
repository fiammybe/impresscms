<?php
/**
 * xoImgUrl Smarty function plug-in for Smarty 5
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
 * @version		$Id: compiler.xoImgUrl.php 506 2006-05-26 23:10:37Z skalpa $
 *
 * Updated for Smarty 5 compatibility - converted to function plugin
 */

/**
 * Inserts the URL of a file resource customizable by themes
 *
 * This plug-in works like the xoAppUrl plug-in, except that it is intended 
 * to generate the URL of resource files customizable by themes.
 *
 * Here the current theme is asked to check if a custom version of the requested 
 * file exists, and if one is found its URL is returned. Otherwise, the request 
 * will be passed to the theme parents one by one. Ultimately, if no custom version 
 * has been found, the resource default URL location will be returned.
 *
 * Example: {xoImgUrl images/logo.png}
 *
 * @param array $params Parameters passed to the function
 * @param object $smarty Smarty template object
 * @return string The generated URL
 */
function smarty_function_xoImgUrl($params, &$smarty) {
	global $xoTheme;
	
	// Get the path - it's passed as the default unnamed parameter
	$path = '';
	foreach ($params as $key => $value) {
		if (is_int($key) || $key === 'path' || $key === '0') {
			$path = $value;
			break;
		}
	}
	
	if (empty($path)) {
		return '';
	}
	
	// Let the theme handle resource path resolution if available
	if (isset($xoTheme) && is_object($xoTheme)) {
		$path = $xoTheme->resourcePath($path);
	}
	
	// Generate the final URL
	return icms::url($path);
}
