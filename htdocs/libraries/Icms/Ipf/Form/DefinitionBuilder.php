<?php
/**
 * The form definition of an IPF entity, from its metadata
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package		Icms\Ipf\Form
 */

namespace Icms\Ipf\Form;

use Icms\Form\Definition\Choice;
use Icms\Form\Definition\ChoiceField;
use Icms\Form\Definition\CompoundField;
use Icms\Form\Definition\Field;
use Icms\Form\Definition\Form;

/**
 * The rules of the IPF form in one place: which variables are hidden, which
 * control each variable gets, its options and its constraints, and the
 * permission selections of the handler.
 *
 * Icms\Ipf\Form\Base creates its elements from plan(); the Symfony form
 * bridge (App\Ipf\Form\IpfEntityType) builds its fields from build(). Both
 * therefore show the same fields, in the same order, with the same options.
 *
 * The entity is anything with the IPF metadata: vars, controls, handler
 * (keyName, getPermissions(), getModuleConfig()), getVar(), isNew() and
 * getGroupPerm().
 */
final class DefinitionBuilder {

	/** a hidden value: the key, or a variable that is not shown on the form */
	public const STEP_HIDDEN = 'hidden';
	/** a control: plan()['control'] names it */
	public const STEP_CONTROL = 'control';
	/** no element (XOBJ_DTYPE_ARRAY without a control) */
	public const STEP_SKIP = 'skip';
	/** no element: the data type has no default control */
	public const STEP_NONE = 'none';

	/**
	 * Controls with a full definition; other controls are Field::legacy()
	 */
	private const DEFINED_CONTROLS = ['text', 'textarea', 'select', 'selectmulti', 'section'];

	public function __construct(private object $entity) {
	}

	/**
	 * What the form does with every variable, in the order of the variables
	 *
	 * - step: STEP_HIDDEN, STEP_CONTROL, STEP_SKIP or STEP_NONE
	 * - control: the IPF control name, for STEP_CONTROL (a control without a
	 *   name is a select)
	 * - set_control: the control the form sets on the entity before it creates
	 *   the element (integers, floats, currencies, closing sections), or null
	 *
	 * @return array<string, array{step: string, control: string|null, set_control: array|null}>
	 */
	public function plan(): array {
		$controls = $this->entity->controls;
		$plan = [];
		foreach ($this->entity->vars as $key => $var) {
			$key = (string) $key;
			if ($key === (string) $this->entity->handler->keyName || !$var['displayOnForm']) {
				$plan[$key] = ['step' => self::STEP_HIDDEN, 'control' => null, 'set_control' => null];
				continue;
			}
			if (isset($controls[$key])) {
				$name = is_array($controls[$key]) ? ($controls[$key]['name'] ?? '') : (string) $controls[$key];
				$plan[$key] = ['step' => self::STEP_CONTROL, 'control' => $name ?: 'select', 'set_control' => null];
				continue;
			}
			$plan[$key] = match ((int) $var['data_type']) {
				XOBJ_DTYPE_TXTBOX => self::control('text'),
				XOBJ_DTYPE_INT, XOBJ_DTYPE_FLOAT => self::control('text', ['name' => 'text', 'size' => '5']),
				XOBJ_DTYPE_CURRENCY => self::control('text', ['name' => 'text', 'size' => '15']),
				XOBJ_DTYPE_LTIME => self::control('datetime'),
				XOBJ_DTYPE_STIME => self::control('date'),
				XOBJ_DTYPE_TIME_ONLY => self::control('time'),
				XOBJ_DTYPE_URLLINK => self::control('urllink'),
				XOBJ_DTYPE_FILE => self::control('richfile'),
				XOBJ_DTYPE_TXTAREA => self::control('textarea'),
				XOBJ_DTYPE_SOURCE => self::control('source'),
				XOBJ_DTYPE_FORM_SECTION => self::control('section'),
				XOBJ_DTYPE_FORM_SECTION_CLOSE => self::control('section', ['close' => true]),
				XOBJ_DTYPE_ARRAY => ['step' => self::STEP_SKIP, 'control' => null, 'set_control' => null],
				default => ['step' => self::STEP_NONE, 'control' => null, 'set_control' => null],
			};
		}
		return $plan;
	}

	/**
	 * The definition of the form: the variables (hidden ones as hidden
	 * values), the permission selections and, when $formName is given, the
	 * actions (op, submit, cancel). The legacy form also posts
	 * icms_page_before_form: navigation, not entity data, so not part of it.
	 */
	public function build(string $formName = '', string $title = '', string $action = ''): Form {
		$rows = $hidden = [];
		foreach ($this->fields() as $field) {
			if ($field->hidden) {
				$hidden[] = $field;
			} else {
				$rows[] = $field;
			}
		}
		foreach ($this->permissions() as $field) {
			$rows[] = $field;
		}
		$actions = [];
		if ($formName !== '') {
			$actions[] = $this->actions($formName);
			$rows[] = $actions[0];
		}
		return new Form($formName, $title, $action, 'post', $rows, $hidden, $actions, 'enctype="multipart/form-data"');
	}

	/**
	 * The fields of the variables, by key, in the order of the variables;
	 * skipped variables have none. A variable without a control of its data
	 * type, or with a control that has no definition yet, is Field::legacy()
	 * (attribute control: the control name, or '' for none).
	 *
	 * @return array<string, Field>
	 */
	public function fields(): array {
		$fields = [];
		foreach ($this->plan() as $key => $step) {
			$field = $this->field($key, $step);
			if ($field !== null) {
				$fields[$key] = $field;
			}
		}
		return $fields;
	}

	/**
	 * The permission selections of the handler: a multiple select of all
	 * groups per permission, with the groups that hold it (for a new entity,
	 * the module's default)
	 *
	 * @return list<ChoiceField>
	 */
	public function permissions(): array {
		$permissions = $this->entity->handler->getPermissions();
		if (!$permissions) {
			return [];
		}
		$groups = \icms::handler('icms_member')->getGroupList();
		asort($groups);
		$config = $this->entity->isNew() ? $this->entity->handler->getModuleConfig() : [];

		$fields = [];
		foreach ($permissions as $permission) {
			$name = (string) $permission['perm_name'];
			$selected = $this->entity->isNew()
				? ($config['def_perm_' . $name] ?? false)
				: $this->entity->getGroupPerm($name);
			$fields[] = new ChoiceField($name, 'select', (string) $permission['caption'], (string) ($permission['description'] ?? ''),
				options: Choice::fromArray($groups),
				selected: array_map('strval', (array) ($selected ?: [])),
				multiple: true,
				size: 4,
				attributes: ['permission' => true]
			);
		}
		return $fields;
	}

	/**
	 * The options of a select control (`options`, `object` + `method`, or a
	 * handler method: `itemHandler` (+ `module`), else the entity's handler,
	 * with `params`), as value => label pairs in display order
	 *
	 * @param	array	$control
	 * @return	array<int|string, string>
	 */
	public function options(array $control): array {
		if (isset($control['options'])) {
			return (array) $control['options'];
		}
		if (isset($control['object'])) {
			$options = method_exists($control['object'], $control['method']) ? $control['object']->{$control['method']}() : [];
			return is_array($options) ? $options : [];
		}
		if (isset($control['itemHandler'])) {
			$handler = isset($control['module'])
				? \icms_getModuleHandler($control['itemHandler'], $control['module'])
				: \icms::handler($control['itemHandler']);
		} else {
			$handler = $this->entity->handler;
		}
		if (!isset($control['method']) || !method_exists($handler, $control['method'])) {
			return [];
		}
		$options = call_user_func_array([$handler, $control['method']], $control['params'] ?? []);
		return is_array($options) ? $options : [];
	}

	/**
	 * The field of one variable, or null if the form has no element for it
	 *
	 * @param	array{step: string, control: string|null, set_control: array|null}	$step
	 */
	private function field(string $key, array $step): ?Field {
		$var = $this->entity->vars[$key];
		$label = (string) ($var['form_caption'] ?? '');
		$description = (string) ($var['form_dsc'] ?? '');
		$required = (bool) $var['required'];

		if ($step['step'] === self::STEP_SKIP) {
			return null;
		}
		if ($step['step'] === self::STEP_HIDDEN) {
			$value = $var['value'];
			return new Field($key, 'hidden', value: $value, hidden: true, attributes: ['multiple' => is_array($value)]);
		}
		if ($step['step'] === self::STEP_NONE || !in_array($step['control'], self::DEFINED_CONTROLS, true)) {
			$legacy = Field::legacy($key, 'ipf:' . ($step['control'] ?? ''), $label, $description, $required);
			return $legacy->with(attributes: ['control' => (string) $step['control'], 'data_type' => (int) $var['data_type']]);
		}

		$control = $step['set_control'] ?? (is_array($this->entity->controls[$key] ?? null) ? $this->entity->controls[$key] : []);
		$common = [
			'label' => $label,
			'description' => $description,
			'required' => $required,
			'disabled' => !empty($var['readonly']),
			'extra' => (string) ($var['form_extra'] ?? ''),
		];
		$dataType = (int) $var['data_type'];

		switch ($step['control']) {
			case 'text':
				// as Icms\Ipf\Form\Elements\Text: without a control 50 wide and 255 long
				$maxlength = $control ? ($control['maxlength'] ?? ($var['maxlength'] ?? 255)) : 255;
				$constraints = [];
				if (!empty($var['maxlength'])) {
					$constraints['maxlength'] = (int) $var['maxlength'];
				}
				if ($dataType === XOBJ_DTYPE_INT) {
					$constraints['integer'] = true;
				} elseif ($dataType === XOBJ_DTYPE_FLOAT || $dataType === XOBJ_DTYPE_CURRENCY) {
					$constraints['number'] = true;
				}
				return new Field($key, 'text', ...$common + [
					'value' => $this->entity->getVar($key, 'e'),
					'attributes' => ['size' => (int) ($control['size'] ?? 50), 'maxlength' => (int) $maxlength, 'data_type' => $dataType],
					'constraints' => $constraints,
				]);

			case 'textarea':
				return new Field($key, 'textarea', ...$common + [
					'value' => $this->entity->getVar($key, 'e'),
					'attributes' => ['id' => $key . '_tarea', 'rows' => (int) ($control['rows'] ?? 5), 'cols' => (int) ($control['cols'] ?? 60), 'data_type' => $dataType],
				]);

			case 'select':
			case 'selectmulti':
				$multiple = $step['control'] === 'selectmulti';
				$value = array_key_exists('value', $control) ? $control['value'] : $this->entity->getVar($key, 'e');
				return new ChoiceField($key, 'select', $label, $description,
					options: Choice::fromArray($this->options($control)),
					selected: array_map('strval', is_array($value) ? $value : ($value === null ? [] : [$value])),
					multiple: $multiple,
					size: (int) ($var['size'] ?? ($multiple ? 5 : 1)),
					required: $required,
					disabled: $common['disabled'],
					attributes: ['data_type' => $dataType],
					extra: $common['extra']
				);

			case 'section':
				$close = !empty($control['close']);
				return new Field($key, $close ? Field::TYPE_SECTION_CLOSE : Field::TYPE_SECTION, $close ? '' : (string) $var['value']);
		}
		return null;
	}

	/**
	 * The actions of the form: the op value and the submit and cancel buttons
	 */
	private function actions(string $formName): CompoundField {
		$submit = $this->entity->isNew() ? ['create_button', _CO_ICMS_CREATE] : ['modify_button', _CO_ICMS_MODIFY];
		return new CompoundField('', 'actions', children: [
			'op' => new Field('op', 'hidden', value: $formName, hidden: true),
			$submit[0] => new Field($submit[0], 'button', value: $submit[1], attributes: ['button_type' => 'submit']),
			'cancel_button' => new Field('cancel_button', 'button', value: _CO_ICMS_CANCEL, attributes: ['button_type' => 'button']),
		]);
	}

	/**
	 * @return array{step: string, control: string, set_control: array|null}
	 */
	private static function control(string $name, ?array $set = null): array {
		return ['step' => self::STEP_CONTROL, 'control' => $name, 'set_control' => $set];
	}
}
