<?php
/**
 * A field with a fixed set of options: select lists, radio buttons, checkboxes
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package		Icms\Form\Definition
 */

namespace Icms\Form\Definition;

class ChoiceField extends Field {

	/**
	 * @param	list<Choice>	$options	in display order
	 * @param	list<string>	$selected	selected values
	 * @param	bool			$multiple	more than one value can be selected
	 * @param	int				$size		visible rows (select lists); 1 is a drop-down
	 */
	public function __construct(
		string $name,
		string $type,
		string $label = '',
		string $description = '',
		public readonly array $options = [],
		public readonly array $selected = [],
		public readonly bool $multiple = false,
		public readonly int $size = 1,
		bool $required = false,
		bool $disabled = false,
		bool $hidden = false,
		array $attributes = [],
		array $constraints = [],
		array $errors = [],
		string $extra = ''
	) {
		parent::__construct($name, $type, $label, $description, $multiple ? $selected : ($selected[0] ?? null), $required, $disabled, $hidden, $attributes, $constraints, $errors, $extra);
	}

	public function isSelected(string $value): bool {
		return in_array($value, $this->selected, true);
	}

	/**
	 * value => label pairs, in display order
	 *
	 * @return array<string, string>
	 */
	public function labels(): array {
		$labels = [];
		foreach ($this->options as $choice) {
			$labels[$choice->value] = $choice->label;
		}
		return $labels;
	}

	/**
	 * Adds `options` (value, label, selected, group), `groups` (label,
	 * options; consecutive options of one group, '' for ungrouped ones),
	 * `selected`, `multiple` and `size`
	 */
	public function toArray(): array {
		$options = [];
		$groups = [];
		foreach ($this->options as $choice) {
			$option = [
				'value' => $choice->value,
				'label' => $choice->label,
				'selected' => $this->isSelected($choice->value),
				'group' => $choice->group,
			];
			$options[] = $option;
			$last = count($groups) - 1;
			if ($last < 0 || $groups[$last]['label'] !== $choice->group) {
				$groups[] = ['label' => $choice->group, 'options' => []];
				$last++;
			}
			$groups[$last]['options'][] = $option;
		}

		return parent::toArray() + [
			'options' => $options,
			'groups' => $groups,
			'selected' => $this->selected,
			'multiple' => $this->multiple,
			'size' => $this->size,
		];
	}

	protected function arguments(): array {
		$arguments = parent::arguments();
		unset($arguments['value']);
		return $arguments + [
			'options' => $this->options,
			'selected' => $this->selected,
			'multiple' => $this->multiple,
			'size' => $this->size,
		];
	}
}
