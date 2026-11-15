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
	 * Current image: type (external or local, empty for none) and url
	 * @var array
	 */
	private $image = array('type' => '', 'url' => '');

	/**
	 * @var icms_form_elements_Text|null
	 */
	private $urlElement = null;

	/**
	 * @var icms_form_elements_Checkbox|null
	 */
	private $deleteElement = null;

	/**
	 * @var icms_ipf_form_elements_Fileupload
	 */
	private $uploadElement;

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
			$this->image = array('type' => 'external', 'url' => str_replace('{ICMS_URL}', ICMS_URL, $value));
		} elseif ($value != '') {
			$this->image = array('type' => 'local', 'url' => $object_imageurl . $value);
		}

		$this->uploadElement = new icms_ipf_form_elements_Fileupload($object, $key);
		$this->addElement($this->uploadElement);

		if (!isset($control['nourl']) || !$control['nourl']) {
			$this->urlElement = new icms_form_elements_Text('', 'url_'.$key, 50, 500);
			$this->addElement($this->urlElement);
		}
		if (!$object->isNew()) {
			$this->deleteElement = new icms_form_elements_Checkbox('', 'delete_'.$key);
			$tpl = new icms_view_Tpl();
			$tpl->assign('text', _CO_ICMS_DELETE);
			$this->deleteElement->addOption(1, $tpl->fetch('db:icms_ipf_form_elements_image_delete.html'));
			$this->addElement($this->deleteElement);
		}
	}

	/**
	 * Prepares the data of the current image, upload field, url field and delete checkbox
	 * and renders them with one template
	 *
	 * @return	string
	 */
	public function render() {
		$items = array();
		if ($this->image['type'] != '') {
			$items[] = array('type' => 'image_' . $this->image['type'], 'url' => $this->image['url']);
		}
		$items[] = array(
			'type' => 'element',
			'caption' => $this->uploadElement->getCaption(),
			'html' => $this->uploadElement->render()
		);
		if ($this->urlElement !== null) {
			$items[] = array('type' => 'url_help', 'text' => _CO_ICMS_URL_FILE_DSC);
			$items[] = array('type' => 'url_label', 'text' => _CO_ICMS_URL_FILE);
			$items[] = array('type' => 'element', 'caption' => '', 'html' => $this->urlElement->render());
		}
		if ($this->deleteElement !== null) {
			$items[] = array('type' => 'break');
			$items[] = array('type' => 'element', 'caption' => '', 'html' => $this->deleteElement->render());
		}

		$tpl = new icms_view_Tpl();
		$tpl->assign(array('items' => $items, 'delimiter' => $this->getDelimeter()));
		return $tpl->fetch('db:icms_ipf_form_elements_image_display.html');
	}
}
