<?php
/**
 * A field made of named parts: image and file uploads, captchas, trays
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package		Icms\Form\Definition
 */

namespace Icms\Form\Definition;

class CompoundField extends Field {

	/**
	 * @param	array<string, Field>	$children	parts by role (e.g. upload, url, delete), in display order
	 */
	public function __construct(
		string $name,
		string $type,
		string $label = '',
		string $description = '',
		public readonly array $children = [],
		mixed $value = null,
		bool $required = false,
		bool $disabled = false,
		bool $hidden = false,
		array $attributes = [],
		array $constraints = [],
		array $errors = [],
		string $extra = ''
	) {
		parent::__construct($name, $type, $label, $description, $value, $required, $disabled, $hidden, $attributes, $constraints, $errors, $extra);
	}

	public function child(string $role): ?Field {
		return $this->children[$role] ?? null;
	}

	/**
	 * Adds `children`: the parts' arrays by role
	 */
	public function toArray(): array {
		$children = [];
		foreach ($this->children as $role => $child) {
			$children[$role] = $child->toArray();
		}
		return parent::toArray() + ['children' => $children];
	}

	protected function arguments(): array {
		return parent::arguments() + ['children' => $this->children];
	}
}
