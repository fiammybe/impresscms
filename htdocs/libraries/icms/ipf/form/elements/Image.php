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

	/** @var string */
	private $_imageKey;

	/** @var string */
	private $_imageSrc;

	/** @var bool */
	private $_imageIsUrl;

	/** @var bool */
	private $_imageHasValue;

	/** @var bool */
	private $_showUrl;

	/** @var bool */
	private $_showDelete;

	/** @var icms_ipf_form_elements_Fileupload */
	private $_fileupload;

	/** @var icms_form_elements_Text|null */
	private $_urlInput;

	/** @var icms_form_elements_Checkbox|null */
	private $_deleteCheckbox;

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

		$this->_imageKey = $key;
		$currentValue = $object->getVar($key, 'e');

		if ($currentValue !== '' && (substr($currentValue, 0, 4) === 'http' || substr($currentValue, 0, 10) === '{ICMS_URL}')) {
			$this->_imageIsUrl = true;
			$this->_imageHasValue = true;
			$this->_imageSrc = str_replace('{ICMS_URL}', ICMS_URL, $currentValue);
		} elseif ($currentValue !== '') {
			$this->_imageIsUrl = false;
			$this->_imageHasValue = true;
			$this->_imageSrc = $object_imageurl . $currentValue;
		} else {
			$this->_imageIsUrl = false;
			$this->_imageHasValue = false;
			$this->_imageSrc = '';
		}

		$this->_fileupload = new icms_ipf_form_elements_Fileupload($object, $key);
		$this->_showUrl = !isset($control['nourl']) || !$control['nourl'];
		$this->_showDelete = !$object->isNew();

		if ($this->_showUrl) {
			$this->_urlInput = new icms_form_elements_Text('', 'url_' . $key, 50, 500);
		}

		if ($this->_showDelete) {
			$this->_deleteCheckbox = new icms_form_elements_Checkbox('', 'delete_' . $key);
			$this->_deleteCheckbox->addOption(1, '<span style="color:red;">' . _CO_ICMS_DELETE . '</span>');
		}
	}

	/**
	 * Prepare HTML for output
	 *
	 * @return string
	 */
	public function render() {
		$this->tpl = new icms_view_Tpl();
		$this->tpl->assign('image_is_url', $this->_imageIsUrl);
		$this->tpl->assign('image_has_value', $this->_imageHasValue);
		$this->tpl->assign('image_src', $this->_imageSrc);
		$this->tpl->assign('fileupload_html', $this->_fileupload->render());
		$this->tpl->assign('show_url', $this->_showUrl);
		$this->tpl->assign('url_file_desc', _CO_ICMS_URL_FILE_DSC);
		$this->tpl->assign('url_file_label', _CO_ICMS_URL_FILE);
		$this->tpl->assign('url_input_html', $this->_showUrl ? $this->_urlInput->render() : '');
		$this->tpl->assign('show_delete', $this->_showDelete);
		$this->tpl->assign('delete_checkbox_html', $this->_showDelete ? $this->_deleteCheckbox->render() : '');

		$element_html_template = $this->customTemplate ? $this->customTemplate : 'icms_ipf_form_elements_image_display.html';
		return $this->tpl->fetch('db:' . $element_html_template);
	}
}