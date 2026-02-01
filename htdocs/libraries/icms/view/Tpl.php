<?php
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
// Author: Kazumi Ono (AKA onokazu)                                          //
// URL: http://www.myweb.ne.jp/, http://www.xoops.org/, http://jp.xoops.org/ //
// Project: The XOOPS Project                                                //
// ------------------------------------------------------------------------- //
/**
 * The templates class that extends Smarty
 *
 * @copyright	http://www.impresscms.org/ The ImpressCMS Project
 * @license		LICENSE.txt
 * @category	ICMS
 * @package		View
 * @subpackage	Templates
 * @author		modified by UnderDog <underdog@impresscms.org>
 * @version		SVN: $Id: Tpl.php 12313 2013-09-15 21:14:35Z skenow $
 */
if (!defined('SMARTY_DIR')) {
	exit();
}
/**
 * Base class: Smarty template engine
 */

use Smarty;
/**
 * Template engine
 *
 * @category	ICMS
 * @package		View
 * @subpackage	Templates
 * @author		Kazumi Ono 	<onokazu@xoops.org>
 * @copyright	Copyright (c) 2000 XOOPS.org
 */
class icms_view_Tpl extends Smarty\Smarty {

	public icms_view_theme_Object $currentTheme;

	public function __construct() {
		global $icmsConfig;

		parent::__construct();

		// Set delimiters using Smarty 5 methods
		$this->setLeftDelimiter('<{');
		$this->setRightDelimiter('}>');

		// Set directories using Smarty 5 methods
		$this->setTemplateDir(ICMS_THEME_PATH);
		$this->setCacheDir(ICMS_CACHE_PATH);
		$this->setCompileDir(ICMS_COMPILE_PATH);

		$this->compile_id = $icmsConfig['template_set'] . '-' . $icmsConfig['theme_set'];
		$this->compile_check = ( $icmsConfig['theme_fromfile'] == 1 );
		$this->addPluginsDir(ICMS_LIBRARIES_PATH . '/smarty/icms_plugins');

		// Register the database resource handler for Smarty 5
		$this->registerResource('db', new \Smarty\Resource\DbPlugin());

		if ($icmsConfig['debug_mode']) {
			$this->debugging_ctrl = 'URL';
			$groups = (is_object(icms::$user)) ? icms::$user->getGroups() : array(ICMS_GROUP_ANONYMOUS);
			$moduleid = (isset(icms::$module) && is_object(icms::$module)) ? icms::$module->getVar('mid') : 1;
			$gperm_handler = icms::handler('icms_member_groupperm');
			if ($icmsConfig['debug_mode'] == 3 && $gperm_handler->checkRight('enable_debug', $moduleid, $groups)) {
				$this->debugging = true;
			}
		}

		if (defined('_ADM_USE_RTL') && _ADM_USE_RTL) {
			$this->assign('icms_rtl', true);
		}

		$this->assign(
			array(
			'icms_url' => ICMS_URL,
			'icms_rootpath' => ICMS_ROOT_PATH,
			'modules_url' => ICMS_MODULES_URL,
			'modules_rootpath' => ICMS_MODULES_PATH,
			'icms_langcode' => _LANGCODE,
			'icms_langname' => $GLOBALS["icmsConfig"]["language"],
			'icms_charset' => _CHARSET,
			'icms_version' => ICMS_VERSION_NAME,
			'icms_upload_url' => ICMS_UPLOAD_URL,
			'xoops_url' => ICMS_URL,
			'xoops_rootpath' => ICMS_ROOT_PATH,
			'xoops_langcode' => _LANGCODE,
			'xoops_charset' => _CHARSET,
			'xoops_version' => ICMS_VERSION_NAME,
			'xoops_upload_url' => ICMS_UPLOAD_URL
			)
		);
	}

	/**
	 * Renders output from template data
	 *
	 * @param   string  $tplSource	The template source to render
	 * @param	bool	$display	If rendered text should be output or returned
	 * @param   array   $vars       Optional variables to assign
	 * @return  string  			Rendered output if $display was false
	 **/
	public function fetchFromData($tplSource, $display = false, $vars = null) {
		// In Smarty 5, we use the 'eval:' resource type for string templates
		if (isset($vars)) {
			// Save current variables
			$oldVars = $this->getTemplateVars();
			// Assign new variables
			$this->assign($vars);
			// Fetch using eval: resource
			$out = $this->fetch('eval:' . $tplSource);
			// Restore old variables
			$this->clearAllAssign();
			if (is_array($oldVars)) {
				$this->assign($oldVars);
			}
			return $out;
		}
		return $this->fetch('eval:' . $tplSource);
	}

	/**
	 * Touch the resource (file) which means get it to recompile the resource
	 *
	 * @param   string  $resourceName	Resourcename to touch
	 * @return  bool    $result         Was the resource recompiled
	 **/
	public function touch($resourceName) {
		$isForced = $this->force_compile;
		$this->force_compile = true;
		$this->clearCache($resourceName);
		$this->clearCompiledTemplate($resourceName);
		// Compile the template
		try {
			$this->fetch($resourceName);
			$result = true;
		} catch (\Exception $e) {
			$result = false;
		}
		$this->force_compile = $isForced;
		return $result;
	}


	/**
	 * function to update compiled template file in templates_c folder
	 *
	 * The proper way to use this would be
	 * icms_view_Tpl::template_touch($tplid);
	 *
	 * @param   string  $tpl_id
	 * @return  boolean
	 **/
	static public function template_touch($tpl_id) {
		$tplfile_handler =& icms::handler('icms_view_template_file');
		$tplfile =& $tplfile_handler->get($tpl_id);

		if (is_object($tplfile)) {
			$file = $tplfile->getVar('tpl_file', 'n');
			$tpl = new icms_view_Tpl();
			return $tpl->touch("db:$file");
		}
		return false;
	}

	/**
	 * Clear the module cache
	 *
	 * The proper way to use this would be
	 * icms_view_Tpl::template_clear_module_cache($tplid);
	 *
	 * @param   int $mid    Module ID
	 * @return
	 **/
	static public function template_clear_module_cache($mid) {
		$icms_block_handler = icms::handler('icms_view_block');
		$block_arr = $icms_block_handler->getByModule($mid);
		$count = count($block_arr);
		if ($count > 0) {
			$xoopsTpl = new icms_view_Tpl();
			$xoopsTpl->caching = 2;
			for ($i = 0; $i < $count; $i++) {
				if ($block_arr[$i]->getVar('template') != '') {
					$xoopsTpl->clearCache('db:'.$block_arr[$i]->getVar('template'), 'blk_'.$block_arr[$i]->getVar('bid'));
				}
			}
		}
	}

}

