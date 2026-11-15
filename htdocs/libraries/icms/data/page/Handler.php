<?php
/**
 * Classes responsible for managing core page objects
 *
 * @copyright	The ImpressCMS Project <http://www.impresscms.org/>
 * @license		LICENSE.txt
 * @category	ICMS
 * @package		Page
 * @since		ImpressCMS 1.1
 * @author		modified by UnderDog <underdog@impresscms.org>
 * @author		Gustavo Pilla (aka nekro) <nekro@impresscms.org> <gpilla@nubee.com.ar>
 * @version		SVN: $Id: Handler.php 10619 2010-09-08 17:59:13Z malanciault $
 */

defined('ICMS_ROOT_PATH') or die('ImpressCMS root path not defined');

/**
 * ImpressCMS page handler class.
 *
 * @since	ImpressCMS 1.2
 * @author	Gustavo Pilla (aka nekro) <nekro@impresscms.org> <gpilla@nubee.com.ar>
 */
class icms_data_page_Handler extends icms_ipf_Handler {

	public function __construct(& $db) {
		parent::__construct($db, 'page', 'page_id', 'page_title', '', 'icms');
		$this->table = $db->prefix('icmspage');
		$this->className = 'icms_data_page_Object';
	}

	public function getList($criteria = null, $limit = 0, $start = 0, $debug = false) {
		$rtn = array();
		$pages =& $this->getObjects($criteria, true);
		foreach ($pages as $page) {
			$rtn[$page->getVar('page_moduleid') . '-' . $page->getVar('page_id')] = $page->getVar('page_title');
		}
		return $rtn;
	}

	public function getPageSelOptions($value = null) {
		if (!is_array($value)) {
			$value = array($value);
		}
		$module_handler = icms::handler('icms_module');
		$criteria = new icms_db_criteria_Compo(new icms_db_criteria_Item('hasmain', 1));
		$criteria->add(new icms_db_criteria_Item('isactive', 1));
		$module_list =& $module_handler->getObjects($criteria);
		$groups = array();
		foreach ($module_list as $module) {
			$criteria = new icms_db_criteria_Compo(new icms_db_criteria_Item('page_moduleid', $module->getVar('mid')));
			$criteria->add(new icms_db_criteria_Item('page_status', 1));
			$pages =& $this->getObjects($criteria);
			$groups[] = $this->_buildPageOptionGroup($module, $pages, $value);
		}

		$module = $module_handler->get(1);
		$criteria = new icms_db_criteria_Compo(new icms_db_criteria_Item('page_moduleid', 1));
		$criteria->add(new icms_db_criteria_Item('page_status', 1));
		$pages =& $this->getObjects($criteria);
		if (count($pages) > 0) {
			array_unshift($groups, $this->_buildPageOptionGroup($module, $pages, $value));
		}

		$tpl = new icms_view_Tpl();
		$tpl->assign(array(
			'top_selected' => in_array('0-1', $value),
			'all_selected' => in_array('0-0', $value),
			'top_label' => _AM_TOPPAGE,
			'all_label' => _AM_ALLPAGES,
			'groups' => $groups
		));
		return $tpl->fetch('db:icms_data_page_seloptions.html');
	}

	/**
	 * Builds the data for an option group (a module and its pages)
	 *
	 * @param	icms_module_Object	$module
	 * @param	array				$pages
	 * @param	array				$value	Selected values
	 * @return	array
	 */
	private function _buildPageOptionGroup($module, $pages, $value) {
		$mid = $module->getVar('mid');
		$options = array(array(
			'value' => $mid . '-0',
			'label' => _AM_ALLPAGES,
			'selected' => in_array($mid . '-0', $value)
		));
		foreach ($pages as $page) {
			$val = $mid . '-' . $page->getVar('page_id');
			$options[] = array(
				'value' => $val,
				'label' => $page->getVar('page_title'),
				'selected' => in_array($val, $value)
			);
		}
		return array('label' => $module->getVar('name'), 'options' => $options);
	}
}
