<?php
/**
 * Shows a page of the Symfony host inside the theme (App\Front\Page)
 *
 * The host runs this script through the legacy bridge with the content it
 * prepared; a request for this file itself finds no page and gets 404.
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		core
 * @since		3.0
 */

$__icms_content = class_exists('App\Front\Page', false) ? \App\Front\Page::take() : null;
if ($__icms_content === null) {
	header('HTTP/1.1 404 Not Found');
	exit();
}

$xoopsOption['pagetype'] = 'user';
unset($xoopsOption['nocommon']);
include dirname(__DIR__) . '/mainfile.php';
// The host's controller may have read mainfile.php already, headless and
// without common.php (for the database settings): then the include above did
// nothing, and the boot runs here.
require_once ICMS_ROOT_PATH . '/include/common.php';
include ICMS_ROOT_PATH . '/header.php';
$__icms_page = $__icms_content();
$icmsTpl->assign('icms_pagetitle', htmlspecialchars($__icms_page['title'], ENT_QUOTES));
echo $__icms_page['html'];
include ICMS_ROOT_PATH . '/footer.php';
