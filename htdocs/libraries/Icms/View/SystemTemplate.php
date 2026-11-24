<?php
/**
 * Renders one of the library's system templates
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package		Icms\View
 */

namespace Icms\View;

/**
 * Library components (form elements, page navigation, links) render their
 * markup with templates of the system module (modules/system/templates), which
 * the template set and themes can override. This class is the one place where
 * such a template is fetched.
 *
 * A template is read through the `db:` resource, so template sets and theme
 * overrides apply. Until the system module is updated, a new template is not
 * in the database yet: then the file in modules/system/templates is used, so
 * that forms keep working (hidden fields and tokens included) during an
 * upgrade.
 *
 * The engine can be replaced (setEngineFactory()), for tests.
 */
final class SystemTemplate {

	/**
	 * @var callable|null	returns a fresh \Smarty that knows the `db:` resource
	 */
	private static $engineFactory = null;

	/**
	 * @var array<string, string>	resolved resource names, by template
	 */
	private static array $resources = [];

	/**
	 * Fetch a system template
	 *
	 * @param	string		$template	file name of the template, e.g. 'icms_form_elements_select_display.html'
	 * @param	array		$vars		template variables
	 * @param	string|null	$override	a template that replaces $template (customTemplate), if not empty
	 * @return	string	the rendered template
	 */
	public static function fetch(string $template, array $vars = [], ?string $override = null): string {
		$tpl = self::engine();
		$tpl->assign($vars);
		$name = ($override !== null && $override !== '') ? $override : $template;
		return (string) $tpl->fetch(self::resource($name, $tpl));
	}

	/**
	 * The resource name for a template: `db:name`, or the system module's file
	 * when the template is not in the database (yet). A name that already
	 * names a resource (`db:`, `file:`) is returned as it is.
	 *
	 * Templates that include another system template use a resource computed
	 * here (passed in a variable), never a literal `db:` include.
	 */
	public static function resource(string $template, ?\Smarty $tpl = null): string {
		if (strpos($template, ':') !== false) {
			return $template;
		}
		if (!isset(self::$resources[$template])) {
			$tpl = $tpl ?? self::engine();
			$file = ICMS_ROOT_PATH . '/modules/system/templates/' . $template;
			self::$resources[$template] = (!$tpl->template_exists('db:' . $template) && is_file($file))
				? 'file:' . $file
				: 'db:' . $template;
		}
		return self::$resources[$template];
	}

	/**
	 * Replace the template engine; null restores the default (Icms\View\Tpl)
	 *
	 * @param	callable|null	$factory	returns a \Smarty instance
	 */
	public static function setEngineFactory(?callable $factory): void {
		self::$engineFactory = $factory;
		self::$resources = [];
	}

	private static function engine(): \Smarty {
		return self::$engineFactory !== null ? (self::$engineFactory)() : new Tpl();
	}
}
