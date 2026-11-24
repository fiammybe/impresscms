<?php
/**
 * One option of a choice field
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package		Icms\Form\Definition
 */

namespace Icms\Form\Definition;

final class Choice {

	/**
	 * @param	string	$value
	 * @param	string	$label	raw label (legacy labels may contain HTML)
	 * @param	string	$group	label of the option group, '' for none
	 */
	public function __construct(
		public readonly string $value,
		public readonly string $label,
		public readonly string $group = ''
	) {
	}

	/**
	 * Choices from value => label pairs, in their order
	 *
	 * @param	array	$options	value => label
	 * @return	list<Choice>
	 */
	public static function fromArray(array $options, string $group = ''): array {
		$choices = [];
		foreach ($options as $value => $label) {
			$choices[] = new self((string) $value, (string) $label, $group);
		}
		return $choices;
	}
}
