<?php
// $Id: cp_functions.php 12313 2013-09-15 21:14:35Z skenow $
// ------------------------------------------------------------------------ //
// XOOPS - PHP Content Management System //
// Copyright (c) 2000 XOOPS.org //
// <http://www.xoops.org/> //
// ------------------------------------------------------------------------ //
// This program is free software; you can redistribute it and/or modify //
// it under the terms of the GNU General Public License as published by //
// the Free Software Foundation; either version 2 of the License, or //
// (at your option) any later version. //
// //
// You may not change or alter any portion of this comment or credits //
// of supporting developers from this source code or any supporting //
// source code which is considered copyrighted (c) material of the //
// original comment or credit authors. //
// //
// This program is distributed in the hope that it will be useful, //
// but WITHOUT ANY WARRANTY; without even the implied warranty of //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the //
// GNU General Public License for more details. //
// //
// You should have received a copy of the GNU General Public License //
// along with this program; if not, write to the Free Software //
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA //
// ------------------------------------------------------------------------ //

/**
 * All control panel functions and forming goes from here.
 * Be careful while editing this file!
 *
 * @copyright The XOOPS Project <http://www.xoops.org/>
 * @copyright The ImpressCMS Project <http://www.impresscms.org/>
 *
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 *
 * @package core
 * @since XOOPS
 * @version $Id: cp_functions.php 12313 2013-09-15 21:14:35Z skenow $
 *
 * @author The XOOPS Project <http://www.xoops.org>
 * @author Sina Asghari (aka stranger) <pesian_stranger@users.sourceforge.net>
 * @author Gustavo Pilla (aka nekro) <nekro@impresscms.org>
 */

/**
 * Be sure this is accessed correctly
 */
defined('ICMS_ROOT_PATH') or die('ImpressCMS root path not defined');
/**
 * Creates constant indicating this file has been loaded
 */
define('XOOPS_CPFUNC_LOADED', 1);

/**
 * Load the template class
 */

/**
 * Function icms_cp_header
 *
 * @since ImpressCMS 1.2
 * @version $Id: cp_functions.php 12313 2013-09-15 21:14:35Z skenow $
 *
 * @author rowd (from the XOOPS Community)
 * @author nekro (aka Gustavo Pilla)<nekro@impresscms.org>
 */
function icms_cp_header() {
	global $icmsConfig, $icmsConfigPlugins, $icmsConfigPersona, $xoopsModule, $xoopsTpl, $xoopsOption, $icmsTheme, $xoTheme, $icmsConfigMultilang, $icmsAdminTpl;

	icms::$logger->stopTime('Module init');
	icms::$logger->startTime('ImpressCMS CP Output Init');

	if (!headers_sent()) {
		header('Content-Type:text/html; charset=' . _CHARSET);
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header("X-Frame-Options: SAMEORIGIN");
	}

	$icmsAdminTpl = new icms_view_Tpl();

	$icmsAdminTpl->assign('xoops_url', ICMS_URL);
	$icmsAdminTpl->assign('icms_sitename', $icmsConfig['sitename']);

	if (@$xoopsOption['template_main']) {
		if (false === strpos($xoopsOption['template_main'], ':')) {
			$xoopsOption['template_main'] = 'db:' . $xoopsOption['template_main'];
		}
	}

	$xoopsThemeFactory = new icms_view_theme_Factory();
	$xoopsThemeFactory->allowedThemes = $icmsConfig['theme_set_allowed'];

	// The next 2 lines are for compatibility only... to implement the admin theme ;)
	// TODO: Remove all this after a few versions!!
	if (isset($icmsConfig['theme_admin_set'])) $xoopsThemeFactory->defaultTheme = $icmsConfig['theme_admin_set'];

	$icmsTheme = $xoTheme = &$xoopsThemeFactory->createInstance(array('contentTemplate' => @$xoopsOption['template_main'],
		'canvasTemplate' => 'theme' . ((file_exists(ICMS_THEME_PATH . '/' . $icmsConfig['theme_admin_set'] . '/theme_admin.html') || file_exists(ICMS_MODULES_PATH . '/system/themes/' . $icmsConfig['theme_admin_set'] . '/theme_admin.html')) ? '_admin' : '') . '.html',
		'plugins' => array('icms_view_PageBuilder'),
		'folderName' => $icmsConfig['theme_admin_set']));
	$icmsAdminTpl = $xoTheme->template;

	// ################# Preload Trigger startOutputInit ##############
	icms::$preload->triggerEvent('adminHeader');

	$xoTheme->addScript(ICMS_URL . '/include/xoops.js', array('type' => 'text/javascript'));
	$xoTheme->addScript('', array('type' => 'text/javascript'), 'startList = function() {
						if (document.all&&document.getElementById) {
							navRoot = document.getElementById("nav");
							for (i=0; i<navRoot.childNodes.length; i++) {
								node = navRoot.childNodes[i];
								if (node.nodeName=="LI") {
									node.onmouseover=function() {
										this.className+=" over";
									}
									node.onmouseout=function() {
										this.className=this.className.replace(" over", "");
									}
								}
							}
						}
					}
					window.onload=startList;');

	// JQuery UI Dialog
	$xoTheme->addScript(ICMS_URL . '/libraries/jquery/jquery.js', array('type' => 'text/javascript'));
	if (!empty($_SESSION['redirect_message'])) {
		$xoTheme->addScript(ICMS_URL . '/libraries/jquery/jgrowl.js', array('type' => 'text/javascript'));
		$xoTheme->addStylesheet(ICMS_URL . '/libraries/jquery/jgrowl' . ((defined('_ADM_USE_RTL') && _ADM_USE_RTL) ? '_rtl' : '') . '.css', array('media' => 'screen'));
		$xoTheme->addScript('', array('type' => 'text/javascript'), '
	if (!window.console || !console.firebug) {
		var names = ["log", "debug", "info", "warn", "error", "assert", "dir", "dirxml", "group", "groupEnd", "time", "timeEnd", "count", "trace", "profile", "profileEnd"];
		window.console = {};

		for (var i = 0; i < names.length; ++i) window.console[names[i]] = function() {};
	}

	(function($) {
		$(document).ready(function() {
			$.jGrowl("' . $_SESSION['redirect_message'] . '", {  life:5000 , position: "center", speed: "slow" });
		});
	})(jQuery);
	');
		unset($_SESSION['redirect_message']);
	}
	$xoTheme->addScript(ICMS_URL . '/libraries/jquery/ui/jquery-ui.min.js', array('type' => 'text/javascript'));
	$xoTheme->addScript(ICMS_URL . '/libraries/jquery/helptip.js', array('type' => 'text/javascript'));
	$xoTheme->addStylesheet(ICMS_URL . '/libraries/jquery/ui/jquery-ui.min.css', array('media' => 'screen'));
	$xoTheme->addStylesheet(ICMS_LIBRARIES_URL . '/jquery/colorbox/colorbox.css');
	$xoTheme->addScript(ICMS_LIBRARIES_URL . '/jquery/colorbox/jquery.colorbox-min.js');

	/*
	 * $jscript = '';
	 * if(class_exists('icms_form_elements_Dhtmltextarea')){
	 * foreach ($icmsConfigPlugins['sanitizer_plugins'] as $key) {
	 * if( empty( $key ) )
	 * continue;
	 * if(file_exists(ICMS_ROOT_PATH.'/plugins/textsanitizer/'.$key.'/'.$key.'.js')){
	 * $xoTheme->addScript(ICMS_URL.'/plugins/textsanitizer/'.$key.'/'.$key.'.js', array('type' => 'text/javascript'));
	 * }else{
	 * $extension = include_once ICMS_ROOT_PATH.'/plugins/textsanitizer/'.$key.'/'.$key.'.php';
	 * $func = 'render_'.$key;
	 * if ( function_exists($func) ) {
	 * @list($encode, $jscript) = $func($ele_name);
	 * if (!empty($jscript)) {
	 * if(!file_exists(ICMS_ROOT_PATH.'/'.$jscript)){
	 * $xoTheme->addScript('', array('type' => 'text/javascript'), $jscript);
	 * }else{
	 * $xoTheme->addScript($jscript, array('type' => 'text/javascript'));
	 * }
	 * }
	 * }
	 * }
	 * }
	 * }
	 */
	$style_info = '';
	if (!empty($icmsConfigPlugins['sanitizer_plugins'])) {
		foreach ($icmsConfigPlugins['sanitizer_plugins'] as $key) {
			if (empty($key)) continue;
			if (file_exists(ICMS_ROOT_PATH . '/plugins/textsanitizer/' . $key . '/' . $key . '.css')) {
				$xoTheme->addStylesheet(ICMS_URL . '/plugins/textsanitizer/' . $key . '/' . $key . '.css', array('media' => 'screen'));
			} else {
				$extension = include_once ICMS_ROOT_PATH . '/plugins/textsanitizer/' . $key . '/' . $key . '.php';
				$func = 'style_' . $key;
				if (function_exists($func)) {
					$style_info = $func();
					if (!empty($style_info)) {
						if (!file_exists(ICMS_ROOT_PATH . '/' . $style_info)) {
							$xoTheme->addStylesheet('', array('media' => 'screen'), $style_info);
						} else {
							$xoTheme->addStylesheet($style_info, array('media' => 'screen'));
						}
					}
				}
			}
		}
	}

	/**
	 * Loading admin dropdown menus
	 */
	if (!file_exists(ICMS_CACHE_PATH . '/adminmenu_' . $icmsConfig['language'] . '.php')) {
		xoops_module_write_admin_menu(impresscms_get_adminmenu());
	}

	$file = file_get_contents(ICMS_CACHE_PATH . "/adminmenu_" . $icmsConfig['language'] . ".php");
	$admin_menu = eval('return ' . $file . ';');

	$moduleperm_handler = icms::handler('icms_member_groupperm');
	$module_handler = icms::handler('icms_module');
	foreach ($admin_menu as $k => $navitem) {
		// Getting array of allowed modules to use in admin home
		if ($navitem['id'] == 'modules') {
			$perm_itens = array();
			foreach ($navitem['menu'] as $item) {
				$module = $module_handler->getByDirname($item['dir']);
				if (!$module) {
					continue;
				}
				$admin_perm = $moduleperm_handler->checkRight(
					'module_admin',
					$module->getVar('mid'),
					icms::$user->getGroups()
				);
				if ($admin_perm) {
					if ($item['dir'] != 'system') {
						$perm_itens[] = $item;
					}
				}
			}
			$navitem['menu'] = $mods = $perm_itens;
		}
		// end
		if ($navitem['id'] == 'opsystem') {
			$groups = icms::$user->getGroups();
			$all_ok = false;
			if (!in_array(ICMS_GROUP_ADMIN, $groups)) {
				$sysperm_handler = icms::handler('icms_member_groupperm');
				$ok_syscats = &$sysperm_handler->getItemIds('system_admin', $groups);
			} else {
				$all_ok = true;
			}
			$perm_itens = array();

			/**
			 * Allow easely change the order of system dropdown menu.
			 * $adminmenuorder = 1; Alphabetically order;
			 * $adminmenuorder = 0; Indice key order;
			 * To change the order when using Indice key order just change the order of the array in the file modules/system/menu.php and after update the system module
			 *
			 * @todo: Create a preference option to set this value and improve the way to change the order.
			 */
			$adminmenuorder = 1;
			$adminsubmenuorder = 1;
			$adminsubsubmenuorder = 1;
			if ($adminmenuorder == 1) {
				foreach ($navitem['menu'] as $k => $sortarray) {
					$column[] = $sortarray['title'];
					if (isset($sortarray['subs']) && count($sortarray['subs']) > 0 && $adminsubmenuorder) {
						asort($navitem['menu'][$k]['subs']);
					}
					if (isset($sortarray['subs']) && count($sortarray['subs']) > 0) {
						foreach ($sortarray['subs'] as $k2 => $sortarray2) {
							if (isset($sortarray2['subs']) && count($sortarray2['subs']) > 0 && $adminsubsubmenuorder) {
								asort($navitem['menu'][$k]['subs'][$k2]['subs']); // Sorting submenus of preferences
							}
						}
					}
				}
				// sort arrays after loop
				array_multisort($column, SORT_ASC, $navitem['menu']);
			}
			foreach ($navitem['menu'] as $item) {
				foreach ($item['subs'] as $key => $subitem) {
					if ($all_ok == false && !in_array($subitem['id'], $ok_syscats)) {
						// remove the subitem
						unset($item['subs'][$key]);
					}
				}
				// only add the item (first layer: groups) if it has subitems
				if (count($item['subs']) > 0) $perm_itens[] = $item;
			}
			// Getting array of allowed system prefs
			$navitem['menu'] = $sysprefs = $perm_itens;
		}
		$icmsAdminTpl->append('navitems', $navitem);
	}

	if (count($sysprefs) > 0) {
		$icmsAdminTpl->assign('systemadm', 1);
	} else {
		$icmsAdminTpl->assign('systemadm', 0);
	}
	if (count($mods) > 0) {
		$icmsAdminTpl->assign('modulesadm', 1);
	} else {
		$icmsAdminTpl->assign('modulesadm', 0);
	}

	/**
	 * Loading options of the current module.
	 */
	if (icms::$module) {
		if (icms::$module->getVar('dirname') == 'system') {
			if (isset($sysprefs) && count($sysprefs) > 0) {
				// remove the grouping for the system module preferences (first layer)
				$sysprefs_tmp = array();
				foreach ($sysprefs as $pref) {
					$sysprefs_tmp = array_merge($sysprefs_tmp, $pref['subs']);
				}
				$sysprefs = $sysprefs_tmp;
				unset($sysprefs_tmp);
				for ($i = count($sysprefs) - 1; $i >= 0; $i = $i - 1) {
					if (isset($sysprefs[$i])) {
						$reversed_sysprefs[] = $sysprefs[$i];
					}
				}
				foreach ($reversed_sysprefs as $k) {
					$icmsAdminTpl->append('mod_options', array('title' => $k['title'], 'link' => $k['link'], 'icon' => (isset($k['icon']) && $k['icon'] != '' ? $k['icon'] : '')));
				}
			}
		} else {
			foreach ($mods as $mod) {
				if ($mod['dir'] == icms::$module->getVar('dirname')) {
					$m = $mod; // Getting info of the current module
					break;
				}
			}
			if (isset($m['subs']) && count($m['subs']) > 0) {
				for ($i = count($m['subs']) - 1; $i >= 0; $i = $i - 1) {
					if (isset($m['subs'][$i])) {
						$reversed_module_admin_menu[] = $m['subs'][$i];
					}
				}
				foreach ($reversed_module_admin_menu as $k) {
					$icmsAdminTpl->append('mod_options', array('title' => $k['title'], 'link' => $k['link'], 'icon' => (isset($k['icon']) && $k['icon'] != '' ? $k['icon'] : '')));
				}
			}
		}
		$icmsAdminTpl->assign('modpath', ICMS_URL . '/modules/' . icms::$module->getVar('dirname'));
		$icmsAdminTpl->assign('modname', icms::$module->getVar('name'));
		$icmsAdminTpl->assign('modid', icms::$module->getVar('mid'));
		$icmsAdminTpl->assign('moddir', icms::$module->getVar('dirname'));
		$icmsAdminTpl->assign('lang_prefs', _PREFERENCES);
	}

	if (@is_object($xoTheme->plugins['icms_view_PageBuilder'])) {
		$aggreg = &$xoTheme->plugins['icms_view_PageBuilder'];

		$icmsAdminTpl->assign_by_ref('xoAdminBlocks', $aggreg->blocks);

		// Backward compatibility code for pre 2.0.14 themes
		$icmsAdminTpl->assign_by_ref('xoops_lblocks', $aggreg->blocks['canvas_left']);
		$icmsAdminTpl->assign_by_ref('xoops_rblocks', $aggreg->blocks['canvas_right']);
		$icmsAdminTpl->assign_by_ref('xoops_ccblocks', $aggreg->blocks['page_topcenter']);
		$icmsAdminTpl->assign_by_ref('xoops_clblocks', $aggreg->blocks['page_topleft']);
		$icmsAdminTpl->assign_by_ref('xoops_crblocks', $aggreg->blocks['page_topright']);

		$icmsAdminTpl->assign('xoops_showlblock', !empty($aggreg->blocks['canvas_left']));
		$icmsAdminTpl->assign('xoops_showrblock', !empty($aggreg->blocks['canvas_right']));
		$icmsAdminTpl->assign('xoops_showcblock', !empty($aggreg->blocks['page_topcenter']) || !empty($aggreg->blocks['page_topleft']) || !empty($aggreg->blocks['page_topright']));

		/**
		 * Send to template some ml infos
		 */
		$icmsAdminTpl->assign('lang_prefs', _PREFERENCES);
		$icmsAdminTpl->assign('ml_is_enabled', $icmsConfigMultilang['ml_enable']);
		$icmsAdminTpl->assign('adm_left_logo', $icmsConfigPersona['adm_left_logo']);
		$icmsAdminTpl->assign('adm_left_logo_url', $icmsConfigPersona['adm_left_logo_url']);
		$icmsAdminTpl->assign('adm_left_logo_alt', $icmsConfigPersona['adm_left_logo_alt']);
		$icmsAdminTpl->assign('adm_right_logo', $icmsConfigPersona['adm_right_logo']);
		$icmsAdminTpl->assign('adm_right_logo_url', $icmsConfigPersona['adm_right_logo_url']);
		$icmsAdminTpl->assign('adm_right_logo_alt', $icmsConfigPersona['adm_right_logo_alt']);
		$icmsAdminTpl->assign('show_impresscms_menu', $icmsConfigPersona['show_impresscms_menu']);
	}
}



/**
 * Function icms_cp_footer
 *
 * @since ImpressCMS 1.2
 * @version $Id: cp_functions.php 12313 2013-09-15 21:14:35Z skenow $
 * @author rowd (from XOOPS Community)
 * @author Gustavo Pilla (aka nekro) <nekro@impresscms.org>
 */
function icms_cp_footer() {
	global $xoopsOption, $xoTheme;
	icms::$logger->stopTime('Module display');

	if (!headers_sent()) {
		header('Content-Type:text/html; charset=' . _CHARSET);
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Cache-Control: private, no-cache');
		header('Pragma: no-cache');
	}
	if (isset($xoopsOption['template_main']) && $xoopsOption['template_main'] != $xoTheme->contentTemplate) {
		trigger_error("xoopsOption[template_main] should be defined before including header.php", E_USER_WARNING);
		if (false === strpos($xoopsOption['template_main'], ':')) {
			$xoTheme->contentTemplate = 'db:' . $xoopsOption['template_main'];
		} else {
			$xoTheme->contentTemplate = $xoopsOption['template_main'];
		}
	}

	icms::$logger->stopTime('XOOPS output init');
	icms::$logger->startTime('Module display');

	$xoTheme->render();

	icms::$logger->stopTime();
	return;
}



function themecenterposts($title, $content) {
	echo '<table cellpadding="4" cellspacing="1" width="98%" class="outer"><tr><td class="head">' . $title . '</td></tr><tr><td><br />' . $content . '<br /></td></tr></table>';
}

/**
 * Creates a multidimensional array with items of the dropdown menus of the admin panel.
 * This array will be saved, by the function xoops_module_write_admin_menu, in a cache file
 * to preserve resources of the server and to maintain compatibility with some modules Xoops.
 *
 * @author TheRplima
 *
 * @return array (content of admin panel dropdown menus)
 */
function impresscms_get_adminmenu() {
	$admin_menu = array();
	$modules_menu = array();
	$systemadm = false;

	# ########################################################################
	# Control Panel Home menu
	# ########################################################################

	$menu[0] = array('link' => ICMS_URL . '/admin.php', 'title' => _CPHOME, 'absolute' => 1, 'small' => ICMS_URL . '/modules/system/images/mini_cp.png');

	$menu[] = array('link' => ICMS_URL, 'title' => _YOURHOME, 'absolute' => 1, 'small' => ICMS_URL . '/modules/system/images/home.png');

	$menu[] = array('link' => ICMS_URL . '/user.php?op=logout', 'title' => _LOGOUT, 'absolute' => 1, 'small' => ICMS_URL . '/modules/system/images/logout.png');

	$admin_menu[0] = array('id' => 'cphome', 'text' => _CPHOME, 'link' => '#', 'menu' => $menu);

	# ########################################################################
	# end
	# ########################################################################

	# ########################################################################
	# System Preferences menu
	# ########################################################################
	$module_handler = icms::handler('icms_module');
	$mod = &$module_handler->getByDirname('system');
	$menu = array();
	foreach ($mod->getAdminMenu() as $lkn) {
		$lkn['dir'] = 'system';
		$menu[] = $lkn;
	}

	$admin_menu[] = array('id' => 'opsystem', 'text' => _SYSTEM, 'link' => ICMS_URL . '/modules/system/admin.php', 'menu' => $menu);
	# ########################################################################
	# end
	# ########################################################################

	# ########################################################################
	# Modules menu
	# ########################################################################
	$module_handler = icms::handler('icms_module');
	$criteria = new icms_db_criteria_Compo();
	$criteria->add(new icms_db_criteria_Item('hasadmin', 1));
	$criteria->add(new icms_db_criteria_Item('isactive', 1));
	$modules = $module_handler->getObjects($criteria);
	usort($modules, 'impresscms_sort_adminmenu_modules');
	foreach ($modules as $module) {
		$rtn = array();
		$inf = &$module->getInfo();
		$rtn['link'] = ICMS_URL . '/modules/' . $module->getVar('dirname') . '/' . (isset($inf['adminindex']) ? $inf['adminindex'] : '');
		$rtn['title'] = $module->getVar('name');
		$rtn['dir'] = $module->getVar('dirname');
		if (isset($inf['iconsmall']) && $inf['iconsmall'] != '') {
			$rtn['small'] = ICMS_URL . '/modules/' . $module->getVar('dirname') . '/' . $inf['iconsmall'];
		}
		if (isset($inf['iconbig']) && $inf['iconbig'] != '') {
			$rtn['iconbig'] = ICMS_URL . '/modules/' . $module->getVar('dirname') . '/' . $inf['iconbig'];
		}
		$rtn['absolute'] = 1;
		$rtn['subs'] = array();
		$module->loadAdminMenu();
		if (is_array($module->adminmenu) && count($module->adminmenu) > 0) {
			foreach ($module->adminmenu as $item) {
				$item['link'] = ICMS_URL . '/modules/' . $module->getVar('dirname') . '/' . $item['link'];
				$rtn['subs'][] = $item;
			}
		}
		$hasconfig = $module->getVar('hasconfig');
		$hascomments = $module->getVar('hascomments');
		if ((isset($hasconfig) && $hasconfig == 1) || (isset($hascomments) && $hascomments == 1)) {
			$subs = array('title' => _PREFERENCES, 'link' => ICMS_URL . '/modules/system/admin.php?fct=preferences&amp;op=showmod&amp;mod=' . $module->getVar('mid'));
			$rtn['subs'][] = $subs;
		}
		$rtn['hassubs'] = (count($rtn['subs']) > 0) ? 1 : 0;
		if ($rtn['hassubs'] == 0) unset($rtn['subs']);
		if ($module->getVar('dirname') == 'system') {
			$systemadm = true;
		}
		$modules_menu[] = $rtn;
	}

	$admin_menu[] = array('id' => 'modules', 'text' => _MODULES, 'link' => ICMS_URL . '/modules/system/admin.php?fct=modulesadmin', 'menu' => $modules_menu);

	# ########################################################################
	# end
	# ########################################################################

	# ########################################################################
	# ImpressCMS News Feed menu
	# ########################################################################
	$menu = array();
	$menu[] = array('link' => 'http://www.impresscms.org', 'title' => _IMPRESSCMS_HOME, 'absolute' => 1 // small' => ICMS_URL . '/images/impresscms.png',
	);

	if (_LANGCODE != 'en') {
		$menu[] = array('link' => _IMPRESSCMS_LOCAL_SUPPORT, 'title' => _IMPRESSCMS_LOCAL_SUPPORT_TITLE, 'absolute' => 1 // 'small' => ICMS_URL . '/images/impresscms.png',
		);
	}

	$menu[] = array('link' => 'https://www.impresscms.org/modules/iforum/', 'title' => _IMPRESSCMS_COMMUNITY, 'absolute' => 1 // 'small' = ICMS_URL . '/images/impresscms.png',
	);

	$menu[] = array('link' => 'https://www.impresscms.org/modules/downloads/', 'title' => _IMPRESSCMS_ADDONS, 'absolute' => 1 // 'small' => ICMS_URL . '/images/impresscms.png',
	);

	$menu[] = array('link' => 'https://www.impresscms.org/modules/simplywiki/', 'title' => _IMPRESSCMS_WIKI, 'absolute' => 1 // 'small' = ICMS_URL . '/images/impresscms.png',
	);

	$menu[] = array('link' => 'https://www.impresscms.org/modules/news/', 'title' => _IMPRESSCMS_BLOG, 'absolute' => 1 // 'small'] = ICMS_URL . '/images/impresscms.png',
	);

	$menu[] = array('link' => 'https://github.com/ImpressCMS/impresscms', 'title' => _IMPRESSCMS_PROJECT, 'absolute' => 1 // 'small' = ICMS_URL . '/images/impresscms.png',
	);
	/*
	 * $menu[] = array(
	 * 'link' => 'http://www.impresscms.org/donations/',
	 * 'title' => _IMPRESSCMS_DONATE,
	 * 'absolute' => 1,
	 * //'small' = ICMS_URL . '/images/impresscms.png',
	 * );
	 */
	$menu[] = array('link' => ICMS_URL . '/admin.php?rssnews=1', 'title' => _IMPRESSCMS_NEWS, 'absolute' => 1 // 'small' => ICMS_URL . '/images/impresscms.png',
	);

	$admin_menu[] = array('id' => 'news', 'text' => _ABOUT, 'link' => '#', 'menu' => $menu);

	# ########################################################################
	# end
	# ########################################################################

	return $admin_menu;
}

function impresscms_sort_adminmenu_modules($a, $b) {
	$a = strtolower($a->getVar("name"));
	$b = strtolower($b->getVar("name"));
	return ($a == $b) ? 0 : (($a < $b) ? -1 : +1);
}

/**
 * Writes entire admin menu into cache
 *
 * @param string $content content to write to the admin menu file
 * @return true
 * @todo create language constants for the error messages
 */
function xoops_module_write_admin_menu($content) {
	global $icmsConfig;
	$filename = ICMS_CACHE_PATH . '/adminmenu_' . $icmsConfig['language'] . '.php';
	if (!$file = fopen($filename, "w")) {
		echo 'failed open file';
		return false;
	}
	if (fwrite($file, var_export($content, true)) == FALSE) {
		echo 'failed write file';
		return false;
	}
	fclose($file);

	// write index.html file in cache folder
	// file is delete after clear_cache (smarty)
	icms_core_Filesystem::writeIndexFile(ICMS_CACHE_PATH);
	return true;
}

// ============================================================================
// Legacy wrapper functions
//
// The functions below provide full backward compatibility with any code that
// calls the icms_cp_* API directly.  Each wrapper instantiates the matching
// PSR-4 class and delegates to the appropriate method.
// ============================================================================

// ----------------------------------------------------------------------------
// Admin UI & Navigation  (Icms\Admin\Ui\*)
// ----------------------------------------------------------------------------

/**
 * Display a generic informational/status message in the admin panel.
 *
 * @param string $message Human-readable message text.
 * @param string $type    Alert type: info|success|warning|danger.
 * @return void
 */
function icms_cp_showmessage(string $message, string $type = 'info'): void
{
	(new \Icms\Admin\Ui\AdminUi())->showMessage($message, $type);
}

/**
 * Display a warning message in the admin panel.
 *
 * @param string $message Human-readable warning text.
 * @return void
 */
function icms_cp_showwarning(string $message): void
{
	(new \Icms\Admin\Ui\AdminUi())->showWarning($message);
}

/**
 * Display an error message in the admin panel.
 *
 * @param string $message Human-readable error text.
 * @return void
 */
function icms_cp_showerror(string $message): void
{
	(new \Icms\Admin\Ui\AdminUi())->showError($message);
}

/**
 * Add a breadcrumb segment to the shared AdminNavigation instance.
 *
 * @param string $title Human-readable breadcrumb label.
 * @param string $url   Link target, or empty string for the active (last) item.
 * @return void
 */
function icms_cp_breadcrumb(string $title, string $url = ''): void
{
	icms_cp_get_navigation()->breadcrumb($title, $url);
}

/**
 * Register a navigation item with the shared AdminNavigation instance.
 *
 * @param string $title Human-readable label.
 * @param string $url   Link target.
 * @param string $icon  Optional icon class or image path.
 * @return void
 */
function icms_cp_addnav(string $title, string $url, string $icon = ''): void
{
	icms_cp_get_navigation()->addNavigationItem($title, $url, $icon);
}

/**
 * Render and return the current navigation HTML from the shared AdminNavigation instance.
 *
 * @return string Rendered HTML fragment.
 */
function icms_cp_rendernav(): string
{
	return icms_cp_get_navigation()->renderNavigation();
}

/**
 * Return the shared AdminNavigation singleton for this request.
 *
 * @return \Icms\Admin\Ui\AdminNavigation
 */
function icms_cp_get_navigation(): \Icms\Admin\Ui\AdminNavigation
{
	static $nav = null;
	if ($nav === null) {
		$nav = new \Icms\Admin\Ui\AdminNavigation();
	}
	return $nav;
}

// ----------------------------------------------------------------------------
// Admin Templating & Theme  (Icms\Admin\Ui\AdminTemplate / AdminTheme)
// ----------------------------------------------------------------------------

/**
 * Render a template resource and return the output as a string.
 *
 * @param string $template Template resource string (e.g. 'db:system_admin.html').
 * @return string Rendered output.
 */
function icms_cp_template(string $template): string
{
	return (new \Icms\Admin\Ui\AdminTemplate())->render($template);
}

/**
 * Assign a template variable on a fresh AdminTemplate instance.
 *
 * Note: for assigning multiple variables you should use the class directly.
 *
 * @param string $name  Template variable name.
 * @param mixed  $value Value to assign.
 * @return void
 */
function icms_cp_assign(string $name, mixed $value): void
{
	(new \Icms\Admin\Ui\AdminTemplate())->assign($name, $value);
}

/**
 * Render a template resource and send the output to the browser.
 *
 * @param string $template Template resource string.
 * @return void
 */
function icms_cp_display(string $template): void
{
	(new \Icms\Admin\Ui\AdminTemplate())->display($template);
}

/**
 * Enqueue a stylesheet on the active admin theme.
 *
 * @param string               $url        Stylesheet URL.
 * @param array<string, mixed> $attributes Optional HTML attributes.
 * @return void
 */
function icms_cp_theme_css(string $url, array $attributes = []): void
{
	(new \Icms\Admin\Ui\AdminTheme())->addCss($url, $attributes);
}

/**
 * Enqueue a JavaScript file on the active admin theme.
 *
 * @param string               $url        Script URL.
 * @param array<string, mixed> $attributes Optional HTML attributes.
 * @param string               $inline     Optional inline script body.
 * @return void
 */
function icms_cp_theme_js(string $url, array $attributes = [], string $inline = ''): void
{
	(new \Icms\Admin\Ui\AdminTheme())->addJs($url, $attributes, $inline);
}

// ----------------------------------------------------------------------------
// Admin Notifications  (Icms\Admin\Ui\AdminNotifications)
// ----------------------------------------------------------------------------

/**
 * Display or queue a success notification.
 *
 * @param string $message Human-readable message text.
 * @param bool   $session Store in session for post-redirect display.
 * @return void
 */
function icms_cp_notify_success(string $message, bool $session = false): void
{
	(new \Icms\Admin\Ui\AdminNotifications())->success($message, $session);
}

/**
 * Display or queue a warning notification.
 *
 * @param string $message Human-readable message text.
 * @param bool   $session Store in session for post-redirect display.
 * @return void
 */
function icms_cp_notify_warning(string $message, bool $session = false): void
{
	(new \Icms\Admin\Ui\AdminNotifications())->warning($message, $session);
}

/**
 * Display or queue an error notification.
 *
 * @param string $message Human-readable message text.
 * @param bool   $session Store in session for post-redirect display.
 * @return void
 */
function icms_cp_notify_error(string $message, bool $session = false): void
{
	(new \Icms\Admin\Ui\AdminNotifications())->error($message, $session);
}

/**
 * Display or queue an informational notification.
 *
 * @param string $message Human-readable message text.
 * @param bool   $session Store in session for post-redirect display.
 * @return void
 */
function icms_cp_notify_info(string $message, bool $session = false): void
{
	(new \Icms\Admin\Ui\AdminNotifications())->info($message, $session);
}

// ----------------------------------------------------------------------------
// Module Management  (Icms\Admin\Module\ModuleManager)
// ----------------------------------------------------------------------------

/**
 * Install a module by directory name.
 *
 * @param string $dirname Module directory name.
 * @return bool
 */
function icms_cp_module_install(string $dirname): bool
{
	return (new \Icms\Admin\Module\ModuleManager())->install($dirname);
}

/**
 * Uninstall a module by directory name.
 *
 * @param string $dirname Module directory name.
 * @return bool
 */
function icms_cp_module_uninstall(string $dirname): bool
{
	return (new \Icms\Admin\Module\ModuleManager())->uninstall($dirname);
}

/**
 * Update (re-install) a module by directory name.
 *
 * @param string $dirname Module directory name.
 * @return bool
 */
function icms_cp_module_update(string $dirname): bool
{
	return (new \Icms\Admin\Module\ModuleManager())->update($dirname);
}

/**
 * Activate a module by directory name.
 *
 * @param string $dirname Module directory name.
 * @return bool
 */
function icms_cp_module_activate(string $dirname): bool
{
	return (new \Icms\Admin\Module\ModuleManager())->activate($dirname);
}

/**
 * Deactivate a module by directory name.
 *
 * @param string $dirname Module directory name.
 * @return bool
 */
function icms_cp_module_deactivate(string $dirname): bool
{
	return (new \Icms\Admin\Module\ModuleManager())->deactivate($dirname);
}

/**
 * Return an array of all installed module objects.
 *
 * @param bool $activeOnly When true only active modules are returned.
 * @return \icms_module_Object[]
 */
function icms_cp_module_list(bool $activeOnly = false): array
{
	return (new \Icms\Admin\Module\ModuleManager())->listModules($activeOnly);
}

// ----------------------------------------------------------------------------
// Module Permissions  (Icms\Admin\Module\ModulePermissions)
// ----------------------------------------------------------------------------

/**
 * Retrieve the group IDs that have admin access to a module.
 *
 * @param int $moduleId Numeric module ID.
 * @return int[]
 */
function icms_cp_module_permissions(int $moduleId): array
{
	return (new \Icms\Admin\Module\ModulePermissions())->getPermissions($moduleId);
}

/**
 * Overwrite the admin-access permissions for a module.
 *
 * @param int   $moduleId Numeric module ID.
 * @param int[] $groupIds Group IDs that should receive admin access.
 * @return bool
 */
function icms_cp_module_setpermissions(int $moduleId, array $groupIds): bool
{
	return (new \Icms\Admin\Module\ModulePermissions())->setPermissions($moduleId, $groupIds);
}

// ----------------------------------------------------------------------------
// Configuration Management  (Icms\Admin\Config\AdminConfigManager)
// ----------------------------------------------------------------------------

/**
 * Retrieve a single configuration value.
 *
 * @param string $key      Configuration key name.
 * @param int    $modId    Module ID (1 = system).
 * @param int    $category Config category ID.
 * @return mixed
 */
function icms_cp_getconfig(string $key, int $modId = 1, int $category = 0): mixed
{
	return (new \Icms\Admin\Config\AdminConfigManager())->get($key, $modId, $category);
}

/**
 * Persist a single configuration value.
 *
 * @param string $key      Configuration key name.
 * @param mixed  $value    New value to store.
 * @param int    $modId    Module ID (1 = system).
 * @param int    $category Config category ID.
 * @return bool
 */
function icms_cp_setconfig(string $key, mixed $value, int $modId = 1, int $category = 0): bool
{
	return (new \Icms\Admin\Config\AdminConfigManager())->set($key, $value, $modId, $category);
}

/**
 * Load all configuration values for a module/category pair.
 *
 * @param int $modId    Module ID (1 = system).
 * @param int $category Config category ID.
 * @return array<string, mixed>
 */
function icms_cp_loadconfig(int $modId = 1, int $category = 0): array
{
	return (new \Icms\Admin\Config\AdminConfigManager())->load($modId, $category);
}

/**
 * Persist an associative array of configuration values.
 *
 * @param array<string, mixed> $data     Key → value pairs to save.
 * @param int                  $modId    Module ID.
 * @param int                  $category Config category ID.
 * @return bool
 */
function icms_cp_saveconfig(array $data, int $modId = 1, int $category = 0): bool
{
	return (new \Icms\Admin\Config\AdminConfigManager())->save($data, $modId, $category);
}

/**
 * Render the HTML configuration form for a module/category.
 *
 * @param int    $modId    Module ID (1 = system).
 * @param int    $category Config category ID.
 * @param string $action   Form action URL.
 * @return string Rendered HTML form.
 */
function icms_cp_configform(int $modId = 1, int $category = 0, string $action = ''): string
{
	return (new \Icms\Admin\Config\AdminConfigManager())->renderForm($modId, $category, $action);
}

// ----------------------------------------------------------------------------
// Routing & Redirects  (Icms\Routing\*)
// ----------------------------------------------------------------------------

/**
 * Redirect the browser to a URL and optionally set a flash message.
 *
 * @param string $url     Target URL.
 * @param string $message Optional flash message.
 * @param int    $status  HTTP status code (default 302).
 * @return never
 */
function icms_cp_redirect(string $url, string $message = '', int $status = 302): never
{
	(new \Icms\Routing\Redirector())->redirect($url, $message, $status);
}

/**
 * Build an absolute URL from a path and optional query parameters.
 *
 * @param string               $path   Relative path.
 * @param array<string, mixed> $params Optional query string parameters.
 * @return string Fully-qualified URL.
 */
function icms_cp_makeuri(string $path, array $params = []): string
{
	return (new \Icms\Routing\UrlGenerator())->make($path, $params);
}

/**
 * Build an admin control-panel URL.
 *
 * @param string               $path   Optional sub-path / fct value.
 * @param array<string, mixed> $params Optional additional query parameters.
 * @return string Fully-qualified admin URL.
 */
function icms_cp_adminurl(string $path = '', array $params = []): string
{
	return (new \Icms\Routing\UrlGenerator())->admin($path, $params);
}

// ----------------------------------------------------------------------------
// Admin System Helpers  (Icms\Admin\Module\ModuleInfo)
// ----------------------------------------------------------------------------

/**
 * Return the full info array for a module.
 *
 * @param string $dirname Module directory name.
 * @return array<string, mixed>|null Module info array, or null.
 */
function icms_cp_getmoduleinfo(string $dirname): ?array
{
	return (new \Icms\Admin\Module\ModuleInfo())->getInfo($dirname);
}

/**
 * Return the version string for a module.
 *
 * @param string $dirname Module directory name.
 * @return string|null Version string, or null.
 */
function icms_cp_getmoduleversion(string $dirname): ?string
{
	return (new \Icms\Admin\Module\ModuleInfo())->getVersion($dirname);
}

/**
 * Return the credits text/URL for a module.
 *
 * @param string $dirname Module directory name.
 * @return string|null Credits text, or null.
 */
function icms_cp_getmodulecredits(string $dirname): ?string
{
	return (new \Icms\Admin\Module\ModuleInfo())->getCredits($dirname);
}

/**
 * Return the help URL for a module.
 *
 * @param string $dirname Module directory name.
 * @return string|null Help URL, or null.
 */
function icms_cp_getmodulehelp(string $dirname): ?string
{
	return (new \Icms\Admin\Module\ModuleInfo())->getHelp($dirname);
}
