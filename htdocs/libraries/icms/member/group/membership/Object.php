<?php
/**
 * Manage memberships
 *
 * @copyright	http://www.impresscms.org/ The ImpressCMS Project
 * @license		LICENSE.txt
 * @category	ICMS
 * @package		Member
 * @subpackage	GroupMembership
 * @author		Kazumi Ono (aka onokazo)
 * @version		SVN: $Id: Object.php 10326 2010-07-11 18:54:25Z malanciault $
 */

defined('ICMS_ROOT_PATH') or die("ImpressCMS root path not defined");

/**
 * membership of a user in a group
 *
 * @author		Kazumi Ono <onokazu@xoops.org>
 * @category	ICMS
 * @package		Member
 * @subpackage	Group
 */

namespace Icms\Member\Group\Membership;

class Object extends \Icms\Core\Object {
	/**
	 * constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->initVar('linkid', XOBJ_DTYPE_INT, null, false);
		$this->initVar('groupid', XOBJ_DTYPE_INT, null, false);
		$this->initVar('uid', XOBJ_DTYPE_INT, null, false);
	}
}
