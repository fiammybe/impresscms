<?php
/**
 * ImpressCMS Backup Manager - backup information object
 *
 * Non-persistent IPF object used to render information about a single backup
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package		System
 * @subpackage	Backup
 * @since		2.0
 */

defined("ICMS_ROOT_PATH") or die("ImpressCMS root path not defined");

/**
 * Holds display information about one backup file (no database table)
 */
class SystemBackupInfo extends icms_ipf_Object {

	/**
	 * Constructor
	 *
	 * @param array $info Backup information as returned by icms_core_Backup::getBackupInfo()
	 */
	public function __construct(array $info = array()) {
		$handler = null;
		parent::__construct($handler);

		$this->initVar('name', XOBJ_DTYPE_TXTBOX, '', false, 255, '', false, 'File');
		$this->initVar('size', XOBJ_DTYPE_TXTBOX, '', false, 50, '', false, 'Size');
		$this->initVar('created', XOBJ_DTYPE_TXTBOX, '', false, 50, '', false, 'Created');
		$this->initVar('files', XOBJ_DTYPE_TXTBOX, '', false, 50, '', false, 'Files');
		$this->initVar('valid_zip', XOBJ_DTYPE_TXTBOX, '', false, 10, '', false, 'Valid ZIP');
		$this->initVar('has_database', XOBJ_DTYPE_TXTBOX, '', false, 10, '', false, 'Contains database');

		if ($info) {
			$this->setVar('name', $info['name']);
			$this->setVar('size', $info['size_formatted']);
			$this->setVar('created', $info['created_formatted']);
			$this->setVar('files', number_format($info['files']));
			$this->setVar('valid_zip', $info['valid_zip'] ? 'Yes' : 'No');
			$this->setVar('has_database', !empty($info['has_database']) ? 'Yes' : 'No');
		}
	}
}
