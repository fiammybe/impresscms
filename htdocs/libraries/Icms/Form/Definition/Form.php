<?php
/**
 * Neutral description of a form
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package		Icms\Form\Definition
 */

namespace Icms\Form\Definition;

/**
 * A form: its metadata, the visible rows in order (sections are rows of type
 * section / section_close), the hidden values and the actions (buttons).
 */
final class Form {

	/**
	 * @param	list<Field>	$rows		visible fields and sections, in display order
	 * @param	list<Field>	$hidden		hidden values
	 * @param	list<Field>	$actions	buttons (also listed in $rows where the layout shows them as a row)
	 * @param	string		$extra		legacy raw attribute string of the form tag
	 */
	public function __construct(
		public readonly string $name,
		public readonly string $title = '',
		public readonly string $action = '',
		public readonly string $method = 'post',
		public readonly array $rows = [],
		public readonly array $hidden = [],
		public readonly array $actions = [],
		public readonly string $extra = ''
	) {
	}

	/**
	 * All fields by name: rows, hidden values and the parts of compound rows
	 *
	 * @return array<string, Field>
	 */
	public function fields(): array {
		$fields = [];
		$add = static function (Field $field) use (&$add, &$fields): void {
			if ($field->name !== '' && !isset($fields[$field->name])) {
				$fields[$field->name] = $field;
			}
			if ($field instanceof CompoundField) {
				foreach ($field->children as $child) {
					$add($child);
				}
			}
		};
		foreach (array_merge($this->rows, $this->hidden) as $field) {
			$add($field);
		}
		return $fields;
	}

	/**
	 * The template variable `form` (without the rendering data the Smarty
	 * adapter adds to every row)
	 *
	 * @return array<string, mixed>
	 */
	public function toArray(): array {
		$list = static fn (array $fields): array => array_map(static fn (Field $field): array => $field->toArray(), $fields);
		return [
			'name' => $this->name,
			'title' => $this->title,
			'action' => $this->action,
			'method' => $this->method,
			'extra' => $this->extra,
			'rows' => $list($this->rows),
			'hidden' => $list($this->hidden),
			'actions' => $list($this->actions),
		];
	}
}
