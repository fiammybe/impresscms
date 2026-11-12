<?php
/**
 * Form control creating an hidden field for an object derived from icms_ipf_Object
 * @todo		Remove the hardcoded height attribute, line breaks, styles
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @category	ICMS
 * @package		ipf
 * @subpackage	form
 * @since		1.1
 * @author		marcan <marcan@impresscms.org>
 * @version		$Id: Image.php 11573 2012-02-16 00:39:30Z skenow $
 */

defined('ICMS_ROOT_PATH') or die("ImpressCMS root path not defined");

class icms_ipf_form_elements_Image extends icms_form_elements_Tray {
	/**
	 * Constructor
	 * @param	object    $object   reference to targetobject (@link icms_ipf_Object)
	 * @param	string    $key      the form name
	 */
	public function __construct($object, $key) {
		$var = $object->vars[$key];
		$control = $object->getControl($key);

		$object_imageurl = $object->getImageDir();
		parent::__construct($var['form_caption'], ' ');

		if (isset($objectArray['image'])){
			$objectArray['image'] = str_replace('{ICMS_URL}', ICMS_URL, $objectArray['image']);
		}

		$value = $object->getVar($key, 'e');
		if ($value != '' && (substr($value, 0, 4) == 'http' || substr($value, 0, 10) == '{ICMS_URL}')) {
			$tpl = new icms_view_Tpl();
			$tpl->assign('image_url', str_replace('{ICMS_URL}', ICMS_URL, $value));
			$this->addElement(new icms_form_elements_Label('', $tpl->fetch('db:icms_ipf_form_elements_image_external.html')));
		} elseif ($value != '') {
			$tpl = new icms_view_Tpl();
			$tpl->assign('image_url', $object_imageurl . $value);
			$this->addElement(new icms_form_elements_Label('', $tpl->fetch('db:icms_ipf_form_elements_image_local.html')));
		}

		$this->addElement(new icms_ipf_form_elements_Fileupload($object, $key));

		if (!isset($control['nourl']) || !$control['nourl']) {
			$tpl = new icms_view_Tpl();
			$tpl->assign('text', _CO_ICMS_URL_FILE_DSC);
			$this->addElement(new icms_form_elements_Label($tpl->fetch('db:icms_ipf_form_elements_image_urlhelp.html'), ''));
			$this->addElement(new icms_form_elements_Label('', '<br />' . _CO_ICMS_URL_FILE));
			$this->addElement(new icms_form_elements_Text('', 'url_'.$key, 50, 500));
		}
		if (!$object->isNew()) {
			$this->addElement(new icms_form_elements_Label('', '<br /><br />'));
			$delete_check = new icms_form_elements_Checkbox('', 'delete_'.$key);
			$tpl = new icms_view_Tpl();
			$tpl->assign('text', _CO_ICMS_DELETE);
			$delete_check->addOption(1, $tpl->fetch('db:icms_ipf_form_elements_image_delete.html'));
			$this->addElement($delete_check);
		}
	}
}