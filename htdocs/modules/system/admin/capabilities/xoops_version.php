<?php
/**
 * ImpressCMS Capabilities: the active provider of each capability, and which
 * capabilities are enabled for which IPF object types
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		System
 * @subpackage	Capabilities
 */

$modversion = array(
	'name' => _MD_AM_CAPABILITIES,
	'version' => "1.0",
	'description' => _MD_AM_CAPABILITIES_DSC,
	'author' => "The ImpressCMS Project",
	'credits' => "The ImpressCMS Project",
	'help' => "",
	'license' => "GPL see LICENSE",
	'official' => 1,
	'image' => "capabilities.png",
	'hasAdmin' => 1,
	'adminpath' => "admin.php?fct=capabilities",
	'category' => ICMS_SYSTEM_CAPABILITIES,
	'group' => _MD_AM_GROUPS_SYSTEMTOOLS);
