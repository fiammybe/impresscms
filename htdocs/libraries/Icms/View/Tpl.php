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
namespace Icms\View;

use Icms\View\Smarty\Setup;

if (!defined('ICMS_ROOT_PATH')) {
	exit();
}

/**
 * Template engine: Smarty 5 configured for ImpressCMS.
 *
 * The Smarty 2 method names that modules used (assign_by_ref, clear_cache,
 * register_function, ...) are kept as deprecated wrappers.
 *
 * @category	ICMS
 * @package		View
 * @subpackage	Templates
 * @author		Kazumi Ono 	<onokazu@xoops.org>
 * @copyright	Copyright (c) 2000 XOOPS.org
 */
class Tpl extends \Smarty\Smarty {

	public \Icms\View\Theme\Entity $currentTheme;

	public function __construct() {
		global $icmsConfig;

		parent::__construct();

		$this->setLeftDelimiter('<{');
		$this->setRightDelimiter('}>');
		$this->setTemplateDir(ICMS_THEME_PATH);
		$this->setCacheDir(ICMS_CACHE_PATH);
		$this->setCompileDir(ICMS_COMPILE_PATH);
		$this->compile_id = $icmsConfig['template_set'] . '-' . $icmsConfig['theme_set'];
		$this->setCompileCheck($icmsConfig['theme_fromfile'] == 1);
		$this->muteUndefinedOrNullWarnings();

		Setup::apply($this);

		if ($icmsConfig['debug_mode']) {
			$this->debugging_ctrl = 'URL';
			$groups = (is_object(\icms::$user)) ? \icms::$user->getGroups() : array(ICMS_GROUP_ANONYMOUS);
			$moduleid = (isset(\icms::$module) && is_object(\icms::$module)) ? \icms::$module->getVar('mid') : 1;
			$gperm_handler = \icms::handler('icms_member_groupperm');
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
	 * @param   string  $tplSource	The template to render
	 * @param	bool	$display	Unused, the output is always returned
	 * @param	array	$vars		Variables that are only visible to this template
	 * @return  string  			Rendered output
	 **/
	public function fetchFromData($tplSource, $display = false, $vars = null) {
		$data = $this->createData($this);
		if (isset($vars)) {
			$data->assign($vars);
		}
		return $this->createTemplate('eval:' . $tplSource, null, null, $data)->fetch();
	}

	/**
	 * Touch the resource (file) which means get it to recompile the resource
	 *
	 * @param   string  $resourceName	Resourcename to touch
	 * @return  bool                    False when the resource could not be compiled
	 **/
	public function touch($resourceName) {
		$this->clearCache($resourceName);
		try {
			$this->createTemplate($resourceName)->compileTemplateSource();
			return true;
		} catch (\Smarty\Exception $e) {
			return false;
		}
	}


	/**
	 * function to update compiled template file in templates_c folder
	 *
	 * The proper way to use this would be
	 * \Icms\View\Tpl::template_touch($tplid);
	 *
	 * @param   string  $tpl_id
	 * @return  boolean
	 **/
	static public function template_touch($tpl_id) {
		$tplfile_handler =& \icms::handler('icms_view_template_file');
		$tplfile =& $tplfile_handler->get($tpl_id);

		if (is_object($tplfile)) {
			$file = $tplfile->getVar('tpl_file', 'n');
			$tpl = new \Icms\View\Tpl();
			return $tpl->touch("db:$file");
		}
		return false;
	}

	/**
	 * Clear the module cache
	 *
	 * The proper way to use this would be
	 * \Icms\View\Tpl::template_clear_module_cache($tplid);
	 *
	 * @param   int $mid    Module ID
	 * @return
	 **/
	static public function template_clear_module_cache($mid) {
		$icms_block_handler = \icms::handler('icms_view_block');
		$block_arr = $icms_block_handler->getByModule($mid);
		$count = count($block_arr);
		if ($count > 0) {
			$xoopsTpl = new \Icms\View\Tpl();
			$xoopsTpl->setCaching(self::CACHING_LIFETIME_SAVED);
			for ($i = 0; $i < $count; $i++) {
				if ($block_arr[$i]->getVar('template') != '') {
					$xoopsTpl->clearCache('db:'.$block_arr[$i]->getVar('template'), 'blk_'.$block_arr[$i]->getVar('bid'));
				}
			}
		}
	}

	/**
	 * Assigns a variable by reference, so that later changes of $value show up in the templates.
	 * Smarty 5 has no equivalent of Smarty 2's assign_by_ref().
	 *
	 * @param	string	$name
	 * @param	mixed	$value
	 * @return	$this
	 */
	public function assignByRef($name, &$value) {
		$variable = new \Smarty\Variable();
		$variable->value = &$value;
		$this->tpl_vars[$name] = $variable;
		return $this;
	}

	/** @deprecated use assignByRef() */
	public function assign_by_ref($tpl_var, &$value) {
		$this->deprecated('assign_by_ref', 'assignByRef');
		return $this->assignByRef($tpl_var, $value);
	}

	/** @deprecated use clearCache() */
	public function clear_cache($tpl_file = null, $cache_id = null, $compile_id = null, $exp_time = null) {
		$this->deprecated('clear_cache', 'clearCache');
		return $this->clearCache($tpl_file, $cache_id, $compile_id, $exp_time);
	}

	/** @deprecated use clearAllCache() */
	public function clear_all_cache($exp_time = null) {
		$this->deprecated('clear_all_cache', 'clearAllCache');
		return $this->clearAllCache($exp_time);
	}

	/** @deprecated use clearCompiledTemplate() */
	public function clear_compiled_tpl($tpl_file = null, $compile_id = null, $exp_time = null) {
		$this->deprecated('clear_compiled_tpl', 'clearCompiledTemplate');
		return $this->clearCompiledTemplate($tpl_file, $compile_id, $exp_time);
	}

	/** @deprecated use getTemplateVars() */
	public function get_template_vars($name = null) {
		$this->deprecated('get_template_vars', 'getTemplateVars');
		return $this->getTemplateVars($name);
	}

	/** @deprecated use templateExists() */
	public function template_exists($tpl_file) {
		$this->deprecated('template_exists', 'templateExists');
		return $this->templateExists($tpl_file);
	}

	/** @deprecated use isCached() */
	public function is_cached($tpl_file, $cache_id = null, $compile_id = null) {
		$this->deprecated('is_cached', 'isCached');
		return $this->isCached($tpl_file, $cache_id, $compile_id);
	}

	/** @deprecated use registerPlugin('function', ...) */
	public function register_function($name, $callback, $cacheable = true) {
		$this->deprecated('register_function', "registerPlugin('function', ...)");
		return $this->registerPlugin('function', $name, $callback, $cacheable);
	}

	/** @deprecated use registerPlugin('modifier', ...) */
	public function register_modifier($name, $callback) {
		$this->deprecated('register_modifier', "registerPlugin('modifier', ...)");
		return $this->registerPlugin('modifier', $name, $callback);
	}

	/** @deprecated use registerPlugin('block', ...) */
	public function register_block($name, $callback, $cacheable = true) {
		$this->deprecated('register_block', "registerPlugin('block', ...)");
		return $this->registerPlugin('block', $name, $callback, $cacheable);
	}

	private function deprecated(string $oldName, string $newName): void {
		trigger_error(
			"Icms\\View\\Tpl::$oldName() is deprecated, use $newName() instead.",
			E_USER_DEPRECATED
		);
	}
}
