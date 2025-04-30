<?php
/**
 * ImpressCMS Cache Management
 *
 * @copyright The ImpressCMS Project http://www.impresscms.org/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package Administration
 * @since 2.0.2
 * @author David Janssens (aka fiammybe) <david.j@impresscms.org>
 */
if (!is_object(icms::$user) || !is_object(icms::$module) || !icms::$user->isAdmin(icms::$module->getVar('mid'))) {
	exit("Access Denied");
}

function editcache($showmenu = FALSE, $cacheid = 0, $clone = FALSE)
{
	global $icmsAdminTpl;

	icms_cp_header();
}

function clearCache()
{
	$cachefolders = ['/templates_c/', '/cache/'];
	foreach ($cachefolders as $folder) {

		if (icms_core_Filesystem::deleteRecursive(ICMS_ROOT_PATH . $folder, FALSE) !== FALSE) {
			icms_core_Filesystem::writeIndexFile(ICMS_ROOT_PATH . $folder);
			printf( "%s\n","Cache $folder cleared\n");

		} else {
			if (!is_file(ICMS_ROOT_PATH . $folder . 'index.html')) {
				icms_core_Filesystem::writeIndexFile(ICMS_ROOT_PATH . $folder);
			}
			echo "missing index file added in $folder";
		}
	}
}

$op = '';
$adsenseid = 0;

$filter_get = array('op' => 'str');

$filter_post = array();

/* filter the user input */
if (!empty($_GET)) {
	// in places where strict mode is not used for checkVarArray, make sure filter_ vars are not overwritten
	if (isset($_GET['filter_post'])) {
		unset($_GET['filter_post']);
	}
	$clean_GET = icms_core_DataFilter::checkVarArray($_GET, $filter_get, false);
	extract($clean_GET);
}

icms_loadLanguageFile('system', 'common');

if (!empty($_POST)) {
	$clean_POST = icms_core_DataFilter::checkVarArray($_POST, $filter_post, false);
	extract($clean_POST);
}
$count = icms_core_Filesystem::getFileCount(ICMS_ROOT_PATH.'/templates_c/') + icms_core_Filesystem::getFileCount(ICMS_ROOT_PATH.'/cache/');;
icms_cp_header();
switch ($op) {
	case "clear":
		clearCache();
		break;
	default:

		$form = new icms_form_Theme("Clear cache", 'cache_form', 'admin.php?fct=cache&op=clear', 'post', TRUE);
		$button = new icms_form_elements_Button('Clear ' . $count . " files from cache", 'button', "Clear cache", 'submit');
		$form->addElement($button);
		$form->display();
		break;
}

icms_cp_footer();