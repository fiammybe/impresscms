<?php
/**
 * Neutral description of one form field
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package		Icms\Form\Definition
 */

namespace Icms\Form\Definition;

/**
 * What a field is, independent of how it is rendered: the legacy Smarty
 * templates and the Symfony form bridge both read it.
 *
 * Values are raw (as the control holds them); templates decide on escaping.
 * Nothing here refers to Smarty, Symfony or HTML, except `extra`: the raw
 * attribute string legacy code attached with Element::setExtra(), kept so
 * that the legacy templates can reproduce it. Other renderers ignore it.
 *
 * Logical types in use (Field::TYPE_*): text, integer, float, password,
 * textarea, hidden, label, button, checkbox, radio, yesno, select, file,
 * image, page, captcha, groupperm, tray, actions, section, section_close and
 * legacy (a control that has no definition yet; see Field::legacy()).
 */
class Field {

	public const TYPE_LEGACY = 'legacy';
	public const TYPE_SECTION = 'section';
	public const TYPE_SECTION_CLOSE = 'section_close';

	/**
	 * @param	string	$name			the submitted name, without a trailing [] for multiple values
	 * @param	string	$type			logical type
	 * @param	string	$label			caption (may contain HTML, as legacy captions do)
	 * @param	string	$description	help text (may contain HTML)
	 * @param	mixed	$value			current value, raw
	 * @param	bool	$required
	 * @param	bool	$disabled		read only
	 * @param	bool	$hidden			not shown as a row (submitted as a hidden value)
	 * @param	array	$attributes		presentation hints: size, maxlength, rows, cols, id, control, ...
	 * @param	array	$constraints	validation rules: maxlength, integer, ... (required is $required)
	 * @param	array	$errors			validation messages for this field
	 * @param	string	$extra			legacy raw attribute string (Element::getExtra())
	 */
	public function __construct(
		public readonly string $name,
		public readonly string $type,
		public readonly string $label = '',
		public readonly string $description = '',
		public readonly mixed $value = null,
		public readonly bool $required = false,
		public readonly bool $disabled = false,
		public readonly bool $hidden = false,
		public readonly array $attributes = [],
		public readonly array $constraints = [],
		public readonly array $errors = [],
		public readonly string $extra = ''
	) {
	}

	/**
	 * A control without a definition: renderers that need one must refuse it
	 * (the legacy renderer shows the control's own HTML instead)
	 *
	 * @param	string	$class	class of the control
	 */
	public static function legacy(string $name, string $class, string $label = '', string $description = '', bool $required = false, bool $hidden = false): self {
		return new self($name, self::TYPE_LEGACY, $label, $description, null, $required, false, $hidden, ['class' => $class]);
	}

	public function isLegacy(): bool {
		return $this->type === self::TYPE_LEGACY;
	}

	public function attribute(string $name, mixed $default = null): mixed {
		return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : $default;
	}

	/**
	 * A copy with other values (named arguments of the constructor)
	 */
	public function with(mixed ...$changes): static {
		$args = array_merge($this->arguments(), $changes);
		return new static(...$args);
	}

	/**
	 * The template variable `field`
	 *
	 * @return array<string, mixed>
	 */
	public function toArray(): array {
		return [
			'name' => $this->name,
			'type' => $this->type,
			'label' => $this->label,
			'description' => $this->description,
			'value' => $this->value,
			'required' => $this->required,
			'disabled' => $this->disabled,
			'hidden' => $this->hidden,
			'attributes' => $this->attributes,
			'constraints' => $this->constraints,
			'errors' => $this->errors,
			'extra' => $this->extra,
		];
	}

	/**
	 * Constructor arguments by name, for with()
	 *
	 * @return array<string, mixed>
	 */
	protected function arguments(): array {
		return [
			'name' => $this->name,
			'type' => $this->type,
			'label' => $this->label,
			'description' => $this->description,
			'value' => $this->value,
			'required' => $this->required,
			'disabled' => $this->disabled,
			'hidden' => $this->hidden,
			'attributes' => $this->attributes,
			'constraints' => $this->constraints,
			'errors' => $this->errors,
			'extra' => $this->extra,
		];
	}
}
