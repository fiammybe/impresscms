<?php
/**
 * Form control creating an advanced file upload element
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @category	ICMS
 * @package		ipf
 * @subpackage	form
 * @since		1.1
 * @author		marcan <marcan@impresscms.org>
 * @version		$Id: File.php 11454 2011-11-25 12:29:04Z sato-san $
 */

defined('ICMS_ROOT_PATH') or die("ImpressCMS root path not defined");

class icms_ipf_form_elements_File extends icms_form_elements_File {
	private $_object;
	private $_key;

	/**
	 * Constructor
	 * @param	object    $object   reference to targetobject (@link icms_ipf_Object)
	 * @param	string    $key      the form name
	 */
	public function __construct($object, $key) {
		$this->_object = $object;
		$this->_key = $key;
		parent::__construct($object->vars[$key]['form_caption'], $key, isset($object->vars[$key]['form_maxfilesize']) ? $object->vars[$key]['form_maxfilesize'] : 0);
		$this->setExtra(" size=30");
	}

	/**
	 * prepare HTML for output
	 *
	 * @return	string	$ret  the constructed HTML
	 */
	public function render() {
		$tpl = new icms_view_Tpl();
		$current = $this->_object->getVar($this->_key);
		$tpl->assign(array(
			'has_file' => ($current != ''),
			'current_file_label' => _CO_ICMS_CURRENT_FILE,
			'file_url' => $this->_object->getUploadDir() . $current,
			'file_name' => $current,
			'max_file_size' => $this->getMaxFileSize(),
			'ele_name' => $this->getName(),
			'ele_extra' => $this->getExtra()
		));
		$ret = $tpl->fetch('db:icms_ipf_form_elements_file_display.html');

		return $ret;
	}
}