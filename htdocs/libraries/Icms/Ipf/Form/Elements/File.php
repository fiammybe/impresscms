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

namespace Icms\Ipf\Form\Elements;

defined('ICMS_ROOT_PATH') or die("ImpressCMS root path not defined");

class File extends \Icms\Form\Elements\File {
	private $_object;
	private $_key;

	/**
	 * Constructor
	 * @param	object    $object   reference to targetobject (@link \icms_ipf_Object)
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
	public function render(): string {
		return $this->renderDefinition();
	}

	/**
	 * The definition: a compound field of type "file" with the file input as
	 * child "upload"; the value is the current file name
	 */
	protected function describe(): \Icms\Form\Definition\Field {
		$current = (string) $this->_object->getVar($this->_key);
		$base = $this->definitionBase('file');
		return new \Icms\Form\Definition\CompoundField(...$base + [
			'children' => ['upload' => new \Icms\Form\Definition\Field($base['name'], 'file', attributes: ['max_file_size' => $this->getMaxFileSize()], extra: $base['extra'])],
			'value' => $current,
			'attributes' => [
				'max_file_size' => $this->getMaxFileSize(),
				'current_url' => $current !== '' ? $this->_object->getUploadDir() . $current : '',
			],
		]);
	}

	protected function definitionTemplate(): ?string {
		return 'icms_ipf_form_elements_file_display.html';
	}

	protected function viewData(): array {
		return ['current_label' => _CO_ICMS_CURRENT_FILE];
	}
}
