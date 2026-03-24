<?php
/**
 * Form control creating a page element for an object derived from icms_ipf_Object
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

defined('ICMS_ROOT_PATH') or die("ImpressCMS root path not defined");

class icms_ipf_form_elements_Page extends icms_form_elements_Tray {
	/**
	 * Constructor
	 * @param	object    $object   reference to targetobject (@link icms_ipf_Object)
	 * @param	string    $key      the form name
	 */
	public function __construct($object, $key) {
		icms_loadLanguageFile('system', 'blocksadmin', TRUE);
		parent::__construct(_AM_VISIBLEIN, ' ', $key . '_visiblein_tray');
		$this->_pageOptions = $this->getPageSelOptions($object->getVar('visiblein'));
	}

	/**
	 * @var array Structured page options for template rendering
	 */
	private $_pageOptions = array();

	/**
	 * Prepare HTML for output using a Smarty template
	 *
	 * @return string
	 */
	public function render() {
		$this->tpl = new icms_view_Tpl();
		$this->tpl->assign('page_options', $this->_pageOptions);

		$element_html_template = $this->customTemplate ? $this->customTemplate : 'icms_ipf_form_elements_page_display.html';
		return $this->tpl->fetch('db:' . $element_html_template);
	}

	/**
	 * Build structured options array for template rendering
	 *
	 * @param mixed $value module-page combination
	 * @return array structured options array with type, value, label, selected keys
	 */
	private function getPageSelOptions($value = null) {
		$icms_page_handler = icms::handler('icms_data_page');
		if (!is_array($value)) {
			$value = array($value);
		}
		$module_handler = icms::handler('icms_module');
		$criteria = new icms_db_criteria_Compo(new icms_db_criteria_Item('hasmain', 1));
		$criteria->add(new icms_db_criteria_Item('isactive', 1));
		$module_list = $module_handler->getObjects($criteria);

		$options = array();

		$sel = in_array('0-1', $value);
		$sel1 = in_array('0-0', $value);
		$options[] = array('type' => 'option', 'value' => '0-1', 'label' => _AM_TOPPAGE, 'selected' => $sel);
		$options[] = array('type' => 'option', 'value' => '0-0', 'label' => _AM_ALLPAGES, 'selected' => $sel1);

		// System module (mid=1) pages
		$system_module = $module_handler->get(1);
		$criteria = new icms_db_criteria_Compo(new icms_db_criteria_Item('page_moduleid', 1));
		$criteria->add(new icms_db_criteria_Item('page_status', 1));
		$system_pages = $icms_page_handler->getObjects($criteria);
		if (count($system_pages) > 0) {
			$options[] = array('type' => 'optgroup_start', 'label' => $system_module->getVar('name'));
			$selected = in_array($system_module->getVar('mid') . '-0', $value);
			$options[] = array('type' => 'option', 'value' => $system_module->getVar('mid') . '-0', 'label' => _AM_ALLPAGES, 'selected' => $selected);
			foreach ($system_pages as $page) {
				$selected = in_array($system_module->getVar('mid') . '-' . $page->getVar('page_id'), $value);
				$options[] = array('type' => 'option', 'value' => $system_module->getVar('mid') . '-' . $page->getVar('page_id'), 'label' => $page->getVar('page_title'), 'selected' => $selected);
			}
			$options[] = array('type' => 'optgroup_end');
		}

		// Other module pages
		foreach ($module_list as $module) {
			// Skip system module (mid=1) as it's already processed separately above
			if ($module->getVar('mid') == 1) {
				continue;
			}
			$options[] = array('type' => 'optgroup_start', 'label' => $module->getVar('name'));
			$selected = in_array($module->getVar('mid') . '-0', $value);
			$options[] = array('type' => 'option', 'value' => $module->getVar('mid') . '-0', 'label' => _AM_ALLPAGES, 'selected' => $selected);
			$criteria = new icms_db_criteria_Compo(new icms_db_criteria_Item('page_moduleid', $module->getVar('mid')));
			$criteria->add(new icms_db_criteria_Item('page_status', 1));
			$pages = $icms_page_handler->getObjects($criteria);
			foreach ($pages as $page) {
				$selected = in_array($module->getVar('mid') . '-' . $page->getVar('page_id'), $value);
				$options[] = array('type' => 'option', 'value' => $module->getVar('mid') . '-' . $page->getVar('page_id'), 'label' => $page->getVar('page_title'), 'selected' => $selected);
			}
			$options[] = array('type' => 'optgroup_end');
		}

		return $options;
	}
}