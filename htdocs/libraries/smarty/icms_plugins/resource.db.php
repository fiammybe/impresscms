<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     resource.db.php
 * Type:     resource
 * Name:     db
 * Purpose:  Fetches templates from a database
 * 
 * Smarty 5 compatible version
 * -------------------------------------------------------------
 */

namespace Smarty\Resource;

use Smarty\Template\Source;
use Smarty\Template;

/**
 * Database Resource Plugin for Smarty 5
 * Fetches templates from ImpressCMS database or filesystem
 */
class DbPlugin extends BasePlugin {

	/**
	 * populate Source Object with metadata from Resource
	 *
	 * @param Source $source source object
	 * @param Template|null $_template template object
	 */
	public function populate(Source $source, ?Template $_template = null) {
		$tpl = $this->getTplInfo($source->name);
		
		if (!$tpl) {
			$source->exists = false;
			$source->timestamp = false;
			return;
		}
		
		$source->exists = true;
		
		if (is_object($tpl)) {
			// Template from database
			$source->timestamp = $tpl->getVar('tpl_lastmodified', 'n');
			$source->uid = 'db:' . $source->name;
		} else {
			// Template from filesystem
			$source->timestamp = filemtime($tpl);
			$source->uid = 'file:' . $tpl;
		}
	}

	/**
	 * Load template's source into current template object
	 *
	 * @param Source $source source object
	 *
	 * @return string template source
	 * @throws \Smarty\Exception if source cannot be loaded
	 */
	public function getContent(Source $source) {
		$tpl = $this->getTplInfo($source->name);
		
		if (!$tpl) {
			throw new \Smarty\Exception("Unable to load template 'db:{$source->name}'");
		}
		
		if (is_object($tpl)) {
			// Template from database
			return $tpl->getVar('tpl_source', 'n');
		} else {
			// Template from filesystem
			return file_get_contents($tpl);
		}
	}

	/**
	 * Determine basename for compiled filename
	 *
	 * @param Source $source source object
	 *
	 * @return string resource's basename
	 */
	public function getBasename(Source $source) {
		return basename($source->name);
	}

	/**
	 * Get template information from database or filesystem
	 * 
	 * @param string $tpl_name Template name
	 * @return mixed Template object, file path, or false
	 */
	protected function getTplInfo($tpl_name) {
		global $icmsConfig;

		static $cache = array();

		if (isset($cache[$tpl_name])) {
			return $cache[$tpl_name];
		}

		$tplset = $icmsConfig['template_set'];
		$theme = isset($icmsConfig['theme_set']) ? $icmsConfig['theme_set'] : 'default';

		$tplfile_handler = \icms::handler('icms_view_template_file');
		
		// If we're not using the "default" template set, then get the templates from the DB
		if ($tplset != "default") {
			$tplobj = $tplfile_handler->getPrefetchedBlock($tplset, $tpl_name);
			if (count($tplobj)) {
				return $cache[$tpl_name] = $tplobj[0];
			}
		}
		
		// If we're using the default tplset, get the template from the filesystem
		$tplobj = $tplfile_handler->getPrefetchedBlock("default", $tpl_name);

		if (!count($tplobj)) {
			return $cache[$tpl_name] = false;
		}
		
		$tplobj = $tplobj[0];
		$module = $tplobj->getVar('tpl_module', 'n');
		$type = $tplobj->getVar('tpl_type', 'n');
		$blockpath = ($type == 'block') ? 'blocks/' : '';
		
		// First, check for an overloaded version within the selected theme folder (support both themes/ and modules/system/themes)
		$themeBase = (is_dir(ICMS_MODULES_PATH . '/system/themes/' . $theme))
			? ICMS_MODULES_PATH . '/system/themes/' . $theme
			: ICMS_THEME_PATH . "/$theme";
		$filepath = $themeBase . "/modules/$module/$blockpath$tpl_name";
		
		if (!file_exists($filepath)) {
			// If no custom version exists, get the tpl from its default location
			$filepath = ICMS_ROOT_PATH . "/modules/$module/templates/$blockpath$tpl_name";
			if (!file_exists($filepath)) {
				return $cache[$tpl_name] = $tplobj;
			}
		}
		
		return $cache[$tpl_name] = $filepath;
	}
}

// Register the resource with Smarty 5
// This will be loaded via class autoloading when needed
