<?php
/**
 * ImpressCMS Mimetypes Administration
 *
 * @copyright   The ImpressCMS Project <https://www.impresscms.org/>
 * @license     GNU General Public License (GPL) <https://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
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

// Initialize the mimetype handler
$mimetypeHandler = icms_getModuleHandler('mimetype', 'system');

// Filter and validate input
$filter_get = ['mimetypeid' => 'int', 'op' => 'string'];
$filter_post = ['mimetypeid' => 'int', 'op' => 'string'];

// Get clean GET/POST variables
$clean_GET = icms_core_DataFilter::checkVarArray($_GET, $filter_get, false);
$clean_POST = icms_core_DataFilter::checkVarArray($_POST, $filter_post, false);

// Extract variables
$op = isset($clean_GET['op']) ? strtolower($clean_GET['op']) : (isset($clean_POST['op']) ? strtolower($clean_POST['op']) : 'default');
$mimetypeid = isset($clean_GET['mimetypeid']) ? $clean_GET['mimetypeid'] : (isset($clean_POST['mimetypeid']) ? $clean_POST['mimetypeid'] : 0);

// Debug output - remove after fixing
error_log("Mimetype Debug - OP: $op, ID: $mimetypeid, GET: " . print_r($_GET, true));

// Main logic based on the 'op' parameter
switch ($op) {
	case 'edit':
	case 'mod':
		error_log("Entering edit/mod case for ID: $mimetypeid");

		icms_cp_header();

		// Get the mimetype object
		$mimetypeObj = $mimetypeHandler->get($mimetypeid);

		if (!$mimetypeObj) {
			error_log("Failed to get mimetype object for ID: $mimetypeid");
			redirect_header('admin.php?fct=mimetype', 3, 'Mimetype not found');
			exit;
		}

		error_log("Got mimetype object, isNew: " . ($mimetypeObj->isNew() ? 'true' : 'false'));

		// Use IPF auto-generated form
		if (!$mimetypeObj->isNew()) {
			$sform = $mimetypeObj->getForm(_CO_ICMS_MIMETYPE_EDIT, 'save');
			$formTitle = _CO_ICMS_MIMETYPE_EDIT_INFO;
		} else {
			$mimetypeObj->setVar('mimetypeid', 0);
			$mimetypeObj->setVar('extension', '');
			$sform = $mimetypeObj->getForm(_CO_ICMS_MIMETYPE_CREATE, 'save');
			$formTitle = _CO_ICMS_MIMETYPE_CREATE_INFO;
		}

		echo '<h1 class="mimetypeTitle">' . $formTitle . '</h1>';
		echo '<div class="mimetypeForm">';
		$sform->display();
		echo '</div>';

		icms_cp_footer();
		exit; // Ensure we don't continue to other cases
		break;

	case 'save':
		$controller = new icms_ipf_Controller($mimetypeHandler);
		$controller->storeFromDefaultForm(_CO_ICMS_MIMETYPE_CREATED, _CO_ICMS_MIMETYPE_MODIFIED);
		break;

	case 'delete':
		$controller = new icms_ipf_Controller($mimetypeHandler);
		$controller->handleObjectDeletion();
		break;

	default:
		icms_cp_header();

		echo '<h1 class="mimetypeTitle">' . _CO_ICMS_MIMETYPES_DSC . '</h1>';

		// Create IPF table view
		$objectTable = new icms_ipf_view_Table($mimetypeHandler);
		$objectTable->addColumn(new icms_ipf_view_Column('name', _GLOBAL_LEFT, 150));
		$objectTable->addColumn(new icms_ipf_view_Column('extension', _GLOBAL_LEFT, 150));
		$objectTable->addColumn(new icms_ipf_view_Column('types', _GLOBAL_LEFT));

		// Add intro button with correct operation
		$objectTable->addIntroButton('edit', 'admin.php?fct=mimetype&amp;op=edit', _CO_ICMS_MIMETYPE_CREATE);
		$objectTable->addQuickSearch(['name', 'extension', 'types']);

		echo '<div class="mimetypeTable">';
		echo $objectTable->fetch();
		echo '</div>';

		icms_cp_footer();
		break;
}
