<?php
/**
 * ImpressCMS Preloads
 *
 * The page is served by the Symfony host at index.php/admin/preloads
 * (App\Controller\System\PreloadController); 'link' points the admin menu there.
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package		System
 * @subpackage	Preloads
 * @since		2.0
 */

$modversion = array(
	'name' => _MD_AM_PRELOADS,
	'version' => "1.0",
	'description' => _MD_AM_PRELOADS_DSC,
	'author' => "The ImpressCMS Project",
	'credits' => "The ImpressCMS Project",
	'help' => "",
	'license' => "GPL see LICENSE",
	'official' => 1,
	'image' => "images/preloads.png",
	'hasAdmin' => 1,
	'adminpath' => "index.php/admin/preloads",
	'link' => "index.php/admin/preloads",
	'category' => XOOPS_SYSTEM_PRELOADS,
	'group' => _MD_AM_GROUPS_SYSTEMTOOLS);
