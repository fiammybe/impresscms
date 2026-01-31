<?php
/**
 * includeq Smarty compiler plug-in for Smarty 5
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
 * @version		$Id: compiler.includeq.php 506 2006-05-26 23:10:37Z skalpa $
 *
 * Updated for Smarty 5 compatibility
 */

namespace Smarty\Compile\Tag;

/**
 * Quick include template plug-in for Smarty 5
 *
 * This plug-in simply wraps the native Smarty 5 include tag.
 * In Smarty 5, the include tag already handles variable scoping efficiently,
 * so the old includeq optimization is no longer needed.
 * This plugin exists only for backward compatibility with templates using {includeq}.
 */
class IncludeqTag extends IncludeTag {
	
	/**
	 * Name of this tag
	 *
	 * @var string
	 */
	protected $tagName = 'includeq';
	
	/**
	 * Compiles code for the {includeq} tag
	 * Simply delegates to the parent IncludeTag compiler
	 *
	 * @param array $args array with attributes from parser
	 * @param \Smarty\Compiler\Template $compiler compiler object
	 * @param array $parameter array with compilation parameter
	 *
	 * @return string compiled code
	 * @throws \Smarty\CompilerException
	 */
	public function compile($args, \Smarty\Compiler\Template $compiler, $parameter = [], $tag = null, $function = null): string {
		// Just use the native include compiler
		return parent::compile($args, $compiler, $parameter, $tag, $function);
	}
}
