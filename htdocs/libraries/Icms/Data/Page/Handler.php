<?php
/**
 * Classes responsible for managing core page objects
 *
 * @copyright   The ImpressCMS Project <http://www.impresscms.org/>
 * @license     LICENSE.txt
 * @category    ICMS
 * @package     Data
 * @subpackage  Page
 * @since       ImpressCMS 1.1
 * @author      modified by UnderDog <underdog@impresscms.org>
 * @author      Gustavo Pilla (aka nekro) <nekro@impresscms.org> <gpilla@nubee.com.ar>
 * @version     SVN: $Id: Handler.php 10619 2010-09-08 17:59:13Z malanciault $
 */

namespace Icms\Data\Page;

defined('ICMS_ROOT_PATH') or die('ImpressCMS root path not defined');

/**
 * Page handler class.
 *
 * @category    ICMS
 * @package     Data
 * @subpackage  Page
 */
class Handler extends \Icms\Ipf\Handler
{
    public function __construct($db)
    {
        parent::__construct($db, 'page', 'page_id', 'page_title', '', 'icms');
        $this->table = $db->prefix('icmspage');
        $this->className = 'icms_data_page_Object';
    }

    public function getList($criteria = null, $limit = 0, $start = 0, $debug = false)
    {
        $rtn = [];
        $pages =& $this->getObjects($criteria, true);
        foreach ($pages as $page) {
            $rtn[$page->getVar('page_moduleid') . '-' . $page->getVar('page_id')] = $page->getVar('page_title');
        }
        return $rtn;
    }

    /**
     * The options of a "visible in" select list, in display order: the top
     * page, all pages, then the pages of the system module (if it has any)
     * and of every active module with a main menu entry, grouped by module
     * (the group is the module name; values are "mid-page_id", "mid-0" is all
     * pages of a module)
     *
     * @return list<\Icms\Form\Definition\Choice>
     */
    public function getPageChoices(): array
    {
        $choices = [new \Icms\Form\Definition\Choice('0-1', _AM_TOPPAGE), new \Icms\Form\Definition\Choice('0-0', _AM_ALLPAGES)];

        $module_handler = \icms::handler('icms_module');
        $system = $this->modulePageChoices($module_handler->get(1));
        if (count($system) > 1) {
            $choices = array_merge($choices, $system);
        }

        $criteria = new \Icms\Db\Criteria\Compo(new \Icms\Db\Criteria\Item('hasmain', 1));
        $criteria->add(new \Icms\Db\Criteria\Item('isactive', 1));
        foreach ($module_handler->getObjects($criteria) as $module) {
            $choices = array_merge($choices, $this->modulePageChoices($module));
        }

        return $choices;
    }

    /**
     * The options of a "visible in" select list, as HTML
     *
     * @param mixed $value selected module-page combinations ("mid-page_id")
     * @return string HTML of the options (icms_data_page_seloptions.html)
     */
    public function getPageSelOptions($value = null)
    {
        $field = new \Icms\Form\Definition\ChoiceField('visiblein', 'page', options: $this->getPageChoices(), selected: array_map('strval', is_array($value) ? $value : [$value]), multiple: true, size: 10);

        return \Icms\View\SystemTemplate::fetch('icms_data_page_seloptions.html', ['field' => $field->toArray()]);
    }

    /**
     * "All pages" of a module, then its active pages, in the module's group
     *
     * @param \Icms\Module\Entity $module
     * @return list<\Icms\Form\Definition\Choice>
     */
    private function modulePageChoices($module): array
    {
        $mid = $module->getVar('mid');
        $group = (string) $module->getVar('name');
        $criteria = new \Icms\Db\Criteria\Compo(new \Icms\Db\Criteria\Item('page_moduleid', $mid));
        $criteria->add(new \Icms\Db\Criteria\Item('page_status', 1));
        $choices = [new \Icms\Form\Definition\Choice($mid . '-0', _AM_ALLPAGES, $group)];
        foreach ($this->getObjects($criteria) as $page) {
            $choices[] = new \Icms\Form\Definition\Choice($mid . '-' . $page->getVar('page_id'), (string) $page->getVar('page_title'), $group);
        }

        return $choices;
    }
}
