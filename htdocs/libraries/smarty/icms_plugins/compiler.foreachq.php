<?php
/**
 * foreachq Smarty compiler plug-in for Smarty 5
 *
 * See the enclosed file LICENSE for licensing information.
 * If you did not receive this file, get it at http://www.fsf.org/copyleft/gpl.html
 *
 * @copyright   The XOOPS project http://www.xoops.org/
 * @license     http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author		Skalpa Keo <skalpa@xoops.org>
 * @package		xos_opal
 * @subpackage	xos_opal_Smarty
 * @since       2.0.14
 * @version		$Id: compiler.foreachq.php 506 2006-05-26 23:10:37Z skalpa $
 *
 * Updated for Smarty 5 compatibility
 */

namespace Smarty\Compile\Tag;

/**
 * Quick foreach template plug-in for Smarty 5
 *
 * This plug-in simply wraps the native Smarty 5 foreach tag.
 * In Smarty 5, the foreach tag is already optimized, so the old foreachq optimization is no longer needed.
 * This plugin exists only for backward compatibility with templates using {foreachq}.
 */
class ForeachqTag extends ForeachTag {
	
	/**
	 * Name of this tag
	 *
	 * @var string
	 */
	protected $tagName = 'foreachq';
	
	/**
	 * Compiles code for the {foreachq} tag
	 * Simply delegates to the parent ForeachTag compiler
	 *
	 * @param array $args array with attributes from parser
	 * @param \Smarty\Compiler\Template $compiler compiler object
	 * @param array $parameter array with compilation parameter
	 *
	 * @return string compiled code
	 * @throws \Smarty\CompilerException
	 */
	public function compile($args, \Smarty\Compiler\Template $compiler, $parameter = [], $tag = null, $function = null): string {
		// Just use the native foreach compiler
		return parent::compile($args, $compiler, $parameter, $tag, $function);
	}
}
