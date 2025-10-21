<?php
/**
 * ImpressCMS Mimetypes
 *
 * @copyright   The ImpressCMS Project <https://www.impresscms.org/>
 * @license     GNU General Public License (GPL) <https://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @package     System
 * @subpackage  Mimetypes
 * @since       1.2
 * @author      David Janssens (fiammybe) based on work by Sina Asghari (aka stranger)
  */

defined("ICMS_ROOT_PATH") || die("ImpressCMS root path not defined");

icms_loadLanguageFile('system', 'mimetype', TRUE);

/**
 * Mimetype management for file handling
 *
 * @package     System
 * @subpackage  Mimetypes
 */
class SystemMimetype extends icms_ipf_Object {
	public $content = FALSE;

	/**
	 * Constructor
	 *
	 * @param object $handler
	 */
	public function __construct(&$handler) {
		parent::__construct($handler);

		$this->quickInitVar('mimetypeid', XOBJ_DTYPE_INT, TRUE);
		$this->quickInitVar('extension', XOBJ_DTYPE_TXTBOX, TRUE, _CO_ICMS_MIMETYPE_EXTENSION, _CO_ICMS_MIMETYPE_EXTENSION_DSC);
		$this->quickInitVar('types', XOBJ_DTYPE_TXTAREA, TRUE, _CO_ICMS_MIMETYPE_TYPES, _CO_ICMS_MIMETYPE_TYPES_DSC);
		$this->quickInitVar('name', XOBJ_DTYPE_TXTBOX, TRUE, _CO_ICMS_MIMETYPE_NAME, _CO_ICMS_MIMETYPE_NAME_DSC);
		$this->quickInitVar('dirname', XOBJ_DTYPE_SIMPLE_ARRAY, TRUE, _CO_ICMS_MIMETYPE_DIRNAME);

		$this->setControl('dirname', [
			'name' => 'selectmulti',
			'itemHandler' => 'icms_module',
			'method' => 'getActive'
		]);
	}

	/**
	 * @see icms_ipf_Object::getVar()
	 * @return mixed Value of the selected property
	 */
	public function getVar($key, $format = 's') {
		if ($format === 's' && in_array($key, [])) {
			return call_user_func([$this, $key]);
		}
		return parent::getVar($key, $format);
	}

	/**
	 * Determines if a variable is a zero length string
	 * @param string $var
	 * @return bool
	 */
	public function emptyString($var) {
		return strlen($var) > 0;
	}

	/**
	 * Get the name property of the selected mimetype
	 * @return string
	 */
	public function getMimetypeName() {
		return $this->getVar('name');
	}

	/**
	 * Get the type of the selected mimetype
	 * @return string
	 */
	public function getMimetypeType() {
		return $this->getVar('types');
	}

	/**
	 * Get the ID of the selected mimetype
	 * @return int
	 */
	public function getMimetypeId() {
		return (int) $this->getVar('mimetypeid');
	}
}

/**
 * Handler for the mimetype object class
 *
 * @package     System
 * @subpackage  Mimetypes
 */
class SystemMimetypeHandler extends icms_ipf_Handler {
	public $objects = FALSE;

	/**
	 * Creates an instance of the mimetype handler
	 *
	 * @param object $db
	 */
	public function __construct($db) {
		parent::__construct($db, 'mimetype', 'mimetypeid', 'mimetypeid', 'name', 'system');
		$this->addPermission('use_extension', _CO_ICMS_MIMETYPE_PERMISSION_VIEW, _CO_ICMS_MIMETYPE_PERMISSION_VIEW_DSC);
	}

	/**
	 * @return array
	 */
	public function UserCanUpload() {
		$handler = new icms_ipf_permission_Handler($this);
		return $handler->getGrantedItems('use_extension');
	}

	/**
	 * Returns a list of mimetypes allowed for the user
	 * @return array
	 */
	public function AllowedMimeTypes() {
		$GrantedItems = $this->UserCanUpload();
		$array = [];

		if (!empty($GrantedItems)) {
			$criteria = new icms_db_criteria_Compo();
			$criteria->add(new icms_db_criteria_Item('mimetypeid', '(' . implode(',', array_keys($GrantedItems)) . ')', 'IN'));

			$objects = $this->getObjects($criteria, TRUE, FALSE);

			foreach ($objects as $object) {
				$types = explode(' ', $object->getVar('types', 'n'));
				$array = array_merge($array, $types);
			}
		}

		return $array;
	}

	/**
	 * @param string $mimetype
	 * @param string $module
	 * @return bool
	 */
	public function AllowedModules($mimetype, $module) {
		$GrantedItems = $this->UserCanUpload();

		$criteria = new icms_db_criteria_Compo(new icms_db_criteria_Item('types', '%' . $mimetype . '%', 'LIKE'));
		$objects = $this->getObjects($criteria, TRUE, FALSE);

		$mimetypeid_allowed = FALSE;
		$dirname_allowed = FALSE;

		foreach ($objects as $object) {
			$mimetypeid = $object->getVar('mimetypeid');
			$dirname = explode('|', $object->getVar('dirname', 'n'));

			if (in_array($mimetypeid, array_keys($GrantedItems))) {
				$mimetypeid_allowed = TRUE;
			}

			if (!empty($module) && in_array($module, $dirname)) {
				$dirname_allowed = TRUE;
			}
		}

		return $mimetypeid_allowed && $dirname_allowed;
	}
}
