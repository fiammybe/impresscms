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
		$tpl = new icms_view_Tpl();
		$tpl->assign('options', icms::handler('icms_data_page')->getPageSelOptions($object->getVar('visiblein')));
		$visible_label = new icms_form_elements_Label('', $tpl->fetch('db:icms_ipf_form_elements_page_display.html'));
		$this->addElement($visible_label);
	}
}
