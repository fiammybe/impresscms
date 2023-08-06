<?php
/**
 * Installer language selection page
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
 * @version		$Id: page_langselect.php 12329 2013-09-19 13:53:36Z skenow $
 */

/**
 *
 */
require_once 'common.inc.php';
if (!defined( 'XOOPS_INSTALL' ) )	exit();

$wizard->setPage( 'langselect' );

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$lang = htmlentities($_REQUEST['lang']);

	$languages = icms_core_Filesystem::getDirList( "./language/" );
	if (!in_array($lang, $languages)) {
		$lang = 'english';
	}
	setcookie( 'xo_install_lang', $lang, time() + 3600, '/', null );

	$wizard->redirectToPage( '+1' );
	exit();
}
$_SESSION = array();
$pageHasForm = true;
$pageHasHelp = true;
$title = LANGUAGE_SELECTION;
$content = "";


$languages = icms_core_Filesystem::getDirList( "./language/" );
$content .= '<div class="list-group">';
foreach ( $languages as $lang) {
	$sel = ( $lang == $wizard->language ) ? ' checked="checked"' : '';
	$content .= "<label class=\"list-group-item d-flex gap-3 py-3\"><input class=\"form-check-input flex-shrink-0\" type=\"radio\" name=\"lang\" id=\"listGroupRadios3\" value=\"$lang\" $sel><span class=\"fi fi-gb fis\"></span><h6 class=\"mb-0\">$lang</h6></label>";
	//$content .= "<div class=\"langselect\" style=\"text-decoration: none;\"><a href=\"javascript:void(0);\" style=\"text-decoration: none;\"><img src=\"../images/flags/$lang.gif\" alt=\"$lang\" /><br />$lang<br /> <input type=\"radio\" name=\"lang\" value=\"$lang\"$sel /></a></div>";
}
$content .= '</label></div>';
$content .= '<fieldset style="text-align: center;">';
$content .= '<div>' . ALTERNATE_LANGUAGE_MSG . '</div>';
$content .= '<div style="text-align: center; margin-top: 5px;"><a href="' . ALTERNATE_LANGUAGE_LNK_URL . '" target="_blank">' . ALTERNATE_LANGUAGE_LNK_MSG . '</a></div>';
$content .= '</fieldset>';

include 'install_tpl.php';