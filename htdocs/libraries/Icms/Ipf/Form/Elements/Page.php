<?php
/**
 * Form control creating a page element for an object derived from \icms_ipf_Object
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @category	ICMS
 * @package		ipf
 * @subpackage	form
 * @since		1.1
 * @author		marcan <marcan@impresscms.org>
 * @version		$Id: Page.php 10721 2010-10-11 19:40:51Z phoenyx $
 */

namespace Icms\Ipf\Form\Elements;

defined('ICMS_ROOT_PATH') or die("ImpressCMS root path not defined");

class Page extends \Icms\Form\Elements\Tray {
	/**
	 * Constructor
	 * @param	object    $object   reference to targetobject (@link \icms_ipf_Object)
	 * @param	string    $key      the form name
	 */
	/**
	 * Selected values ("mid-page_id")
	 * @var array
	 */
	private $_visiblein = array();

	public function __construct($object, $key) {
		\icms_loadLanguageFile('system', 'blocksadmin', TRUE);
		parent::__construct(_AM_VISIBLEIN, ' ', $key . '_visiblein_tray');
		$value = $object->getVar('visiblein');
		$this->_visiblein = is_array($value) ? $value : array($value);
	}

	/**
	 * Renders the "visible in" select list (icms_ipf_form_elements_page_display.html)
	 */
	public function render(): string {
		return $this->renderDefinition() . "\n";
	}

	/**
	 * The definition: a choice field of type "page", named visiblein, with the
	 * pages grouped by module (Icms\Data\Page\Handler::getPageChoices())
	 */
	protected function describe(): \Icms\Form\Definition\Field {
		return new \Icms\Form\Definition\ChoiceField('visiblein', 'page', $this->getCaption(), $this->getDescription(),
			options: \icms::handler('icms_data_page')->getPageChoices(),
			selected: array_map('strval', $this->_visiblein),
			multiple: true,
			size: 10,
			required: $this->isRequired()
		);
	}

	protected function definitionTemplate(): ?string {
		return 'icms_ipf_form_elements_page_display.html';
	}
}
