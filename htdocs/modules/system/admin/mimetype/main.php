<?php
/**
 * ImpressCMS Mimetypes Administration
 *
 * @copyright   The ImpressCMS Project <http://www.impresscms.org/>
 * @license     GNU General Public License (GPL) <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @package     Administration
 * @subpackage  Mimetypes
 * @since       1.2
 */

// Check if the user has admin rights for the current module
if (!is_object(icms::$user) || !is_object(icms::$module) || !icms::$user->isAdmin(icms::$module->getVar('mid'))) {
	exit("Access Denied");
}

// Load language files
icms_loadLanguageFile('system', 'common');
icms_loadLanguageFile('system', 'mimetype');

// Initialize the mimetype handler
$mimetypeHandler = icms_getModuleHandler('mimetype', 'system');

// Filter and validate input
$filter_get = ['mimetypeid' => 'int', 'op' => 'str'];
$filter_post = ['mimetypeid' => 'int', 'op' => 'str'];

// Get clean GET/POST variables
$clean_GET = icms_core_DataFilter::checkVarArray($_GET, $filter_get, false);
$clean_POST = icms_core_DataFilter::checkVarArray($_POST, $filter_post, false);

// Extract variables
$op = isset($clean_POST['op']) ? strtolower($clean_POST['op']) : (isset($clean_GET['op']) ? strtolower($clean_GET['op']) : 'default');
$mimetypeid = isset($clean_POST['mimetypeid']) ? $clean_POST['mimetypeid'] : (isset($clean_GET['mimetypeid']) ? $clean_GET['mimetypeid'] : 0);

// Main logic based on the 'op' parameter
switch ($op) {
	case 'mod':
		icms_cp_header();

		// Get the mimetype object
		$mimetypeObj = $mimetypeHandler->get($mimetypeid);

		// Determine if it's a new object
		if (!$mimetypeObj->isNew()) {
			$sform = $mimetypeObj->getForm(_CO_ICMS_MIMETYPE_EDIT, 'addmimetype', 'admin.php?fct=mimetype');
			$formTitle = _CO_ICMS_MIMETYPE_EDIT_INFO;
		} else {
			$mimetypeObj->setVar('mimetypeid', 0);
			$mimetypeObj->setVar('extension', '');
			$sform = $mimetypeObj->getForm(_CO_ICMS_MIMETYPE_CREATE, 'addmimetype', 'admin.php?fct=mimetype');
			$formTitle = _CO_ICMS_MIMETYPE_CREATE_INFO;
		}

		$sform->assign($icmsAdminTpl);
		$icmsAdminTpl->assign('icms_mimetype_title', $formTitle);
		$icmsAdminTpl->display('db:system_adm_mimetype.html');

		icms_cp_footer();
		break;

	case 'addmimetype':
		$controller = new icms_ipf_Controller($mimetypeHandler);
		$controller->storeFromDefaultForm(_CO_ICMS_MIMETYPE_CREATED, _CO_ICMS_MIMETYPE_MODIFIED);
		break;

	case 'del':
		$controller = new icms_ipf_Controller($mimetypeHandler);
		$controller->handleObjectDeletion();
		break;

	default:
		icms_cp_header();

		echo '<h1 class="mimetypeTitle">' . _CO_ICMS_MIMETYPES_DSC . '</h1>';

		// Create a table view for the mimetypes
		$objectTable = new icms_ipf_view_Table($mimetypeHandler);
		$objectTable->addColumn(new icms_ipf_view_Column('name', _GLOBAL_LEFT, 150));
		$objectTable->addColumn(new icms_ipf_view_Column('extension', _GLOBAL_LEFT, 150));
		$objectTable->addColumn(new icms_ipf_view_Column('types', _GLOBAL_LEFT));

		// Add buttons and search functionality
		$objectTable->addIntroButton('addmimetype', 'admin.php?fct=mimetype&amp;op=mod', _CO_ICMS_MIMETYPE_CREATE);
		$objectTable->addQuickSearch(['name', 'extension', 'types']);

		// Render the table directly
		echo '<div class="mimetypeTable">';
		echo $objectTable->fetch();
		echo '</div>';

		icms_cp_footer();
		break;
}
