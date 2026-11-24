<?php
/**
 * A control that can describe itself as a field definition
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package		Icms\Form\Definition
 */

namespace Icms\Form\Definition;

/**
 * Every Icms\Form\Element implements this. A control of a module opts in to
 * a real definition by overriding Element::describe() in the class that also
 * renders it; until then its definition is Field::legacy().
 */
interface Describable {

	public function getDefinition(): Field;
}
