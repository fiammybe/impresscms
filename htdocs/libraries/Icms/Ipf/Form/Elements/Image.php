<?php
/**
 * Form control creating an hidden field for an object derived from \icms_ipf_Object
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

namespace Icms\Ipf\Form\Elements;

defined('ICMS_ROOT_PATH') or die("ImpressCMS root path not defined");

class Image extends \Icms\Form\Elements\Tray {
	/**
	 * The current image: type (external, local or '' for none) and url
	 * @var array
	 */
	private $_image = array('type' => '', 'url' => '');

	/**
	 * @var \Icms\Ipf\Form\Elements\Fileupload
	 */
	private $_upload;

	/**
	 * @var \Icms\Form\Elements\Text|null
	 */
	private $_url = null;

	/**
	 * @var \Icms\Form\Elements\Checkbox|null
	 */
	private $_delete = null;

	/**
	 * @var string	the variable
	 */
	private $_key;

	/**
	 * Constructor
	 * @param	object    $object   reference to targetobject (@link icms_ipf_Object)
	 * @param	string    $key      the form name
	 */
	public function __construct($object, $key) {
		$this->_key = (string) $key;
		$var = $object->vars[$key];
		$control = $object->getControl($key);
		$object_imageurl = $object->getImageDir();
		parent::__construct($var['form_caption'], ' ');

		$value = $object->getVar($key, 'e');
		if ($value != '' && (substr($value, 0, 4) == 'http' || substr($value, 0, 10) == '{ICMS_URL}')) {
			$this->_image = array('type' => 'external', 'url' => str_replace('{ICMS_URL}', ICMS_URL, $value));
		} elseif ($value != '') {
			$this->_image = array('type' => 'local', 'url' => $object_imageurl . $value);
		}

		$this->_upload = new \Icms\Ipf\Form\Elements\Fileupload($object, $key);
		$this->addElement($this->_upload);

		if (!isset($control['nourl']) || !$control['nourl']) {
			$this->_url = new \Icms\Form\Elements\Text('', 'url_' . $key, 50, 500);
			$this->addElement($this->_url);
		}
		if (!$object->isNew()) {
			$this->_delete = new \Icms\Form\Elements\Checkbox('', 'delete_' . $key);
			$this->_delete->addOption(1, trim(\Icms\View\SystemTemplate::fetch('icms_ipf_form_elements_image_delete.html', ['text' => _CO_ICMS_DELETE])));
			$this->addElement($this->_delete);
		}
	}

	/**
	 * Renders the current image, the upload field, the url field and the delete
	 * checkbox (icms_ipf_form_elements_image_display.html)
	 */
	public function render(): string {
		return $this->renderDefinition();
	}

	/**
	 * The definition: a compound field of type "image" with the children
	 * upload, url (unless the control has nourl) and delete (for an existing
	 * object); attributes image_type (external, local or '') and image_url
	 */
	protected function describe(): \Icms\Form\Definition\Field {
		$children = ['upload' => $this->_upload->getDefinition()];
		if ($this->_url !== null) {
			$children['url'] = $this->_url->getDefinition();
		}
		if ($this->_delete !== null) {
			$children['delete'] = $this->_delete->getDefinition();
		}
		return new \Icms\Form\Definition\CompoundField(...array_merge($this->definitionBase('image'), [
			'name' => $this->_key,
			'children' => $children,
			'value' => $this->_image['url'],
			'attributes' => ['image_type' => $this->_image['type'], 'image_url' => $this->_image['url'], 'delimiter' => $this->getDelimeter()],
		]));
	}

	protected function definitionTemplate(): ?string {
		return 'icms_ipf_form_elements_image_display.html';
	}

	/**
	 * The parts in display order (kind: image, element, url_help, url_label
	 * or break; element parts have the control array of the child)
	 */
	protected function viewData(): array {
		$parts = array();
		if ($this->_image['type'] !== '') {
			$parts[] = array('kind' => 'image');
		}
		$parts[] = array('kind' => 'element', 'control' => \Icms\Form\View\FormView::control($this->_upload));
		if ($this->_url !== null) {
			$parts[] = array('kind' => 'url_help', 'text' => _CO_ICMS_URL_FILE_DSC);
			$parts[] = array('kind' => 'url_label', 'text' => _CO_ICMS_URL_FILE);
			$parts[] = array('kind' => 'element', 'control' => \Icms\Form\View\FormView::control($this->_url));
		}
		if ($this->_delete !== null) {
			$parts[] = array('kind' => 'break');
			$parts[] = array('kind' => 'element', 'control' => \Icms\Form\View\FormView::control($this->_delete));
		}
		return array('parts' => $parts);
	}
}
