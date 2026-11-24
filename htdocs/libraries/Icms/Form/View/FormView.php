<?php
/**
 * Smarty adapter: template data for forms and controls, from their definitions
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package		Icms\Form\View
 */

namespace Icms\Form\View;

use Icms\Form\Definition\Field;
use Icms\View\SystemTemplate;

/**
 * Turns form definitions into the data the form templates receive.
 *
 * Every control becomes a "control" array:
 *
 * - type: the logical type of its definition (or 'raw' for HTML added to a form as a string)
 * - field: its definition (Field::toArray())
 * - view: presentation data its template needs besides the definition
 * - template: the resource of the template that renders it from `field` and
 *   `view`, for controls that have one; layouts include it
 * - html: the control's own HTML, only for controls without such a template
 *   (legacy controls, released templates with their own variables, raw rows)
 *
 * A layout renders a control with
 * <{if $row.template}><{include file=$row.template field=$row.field view=$row.view}><{else}><{$row.html}><{/if}>
 */
final class FormView {

	/**
	 * The control array of an element
	 *
	 * @return array{type: string, field: array<string, mixed>, view: array<string, mixed>, template: string|null, html: string|null}
	 */
	public static function control(\Icms\Form\Element $element): array {
		$template = $element->getDefinitionTemplate();
		if ($template !== null) {
			$vars = $element->getDefinitionTemplateVars();
			return [
				'type' => (string) $vars['field']['type'],
				'field' => $vars['field'],
				'view' => $vars['view'],
				'template' => SystemTemplate::resource($template),
				'html' => null,
			];
		}
		$field = $element->getDefinition();
		return [
			'type' => $field->type,
			'field' => $field->toArray(),
			'view' => [],
			'template' => null,
			'html' => $element->render(),
		];
	}

	/**
	 * The control array of HTML that was added to a form as a string
	 *
	 * @return array{type: string, field: null, view: array<string, mixed>, template: null, html: string}
	 */
	public static function raw(string $html): array {
		return ['type' => 'raw', 'field' => null, 'view' => [], 'template' => null, 'html' => $html];
	}

	/**
	 * The template variable `form` of a form layout
	 *
	 * - name, title, action, method, extra: the form tag (extra is the legacy raw attribute string)
	 * - elements: control arrays of all elements, in order (hidden ones included)
	 * - rows: the visible controls and raw rows, in order
	 * - hidden: the hidden controls, in order
	 * - validation: required (names of the required fields), javascript (the
	 *   legacy validation function, without script tags; a compatibility
	 *   value until validation moves to the definitions)
	 * - definition: the form definition (Definition\Form::toArray())
	 *
	 * @param	array<string, mixed>	$extra	additional keys
	 * @return	array<string, mixed>
	 */
	public static function form(\Icms\Form\Base $form, array $extra = []): array {
		$elements = $rows = $hidden = [];
		foreach ($form->getElements() as $element) {
			if (!is_object($element)) {
				$control = self::raw((string) $element);
				$control['hidden'] = false;
				$rows[] = $control;
			} else {
				$control = self::control($element);
				$control['hidden'] = $element->isHidden();
				if ($control['hidden']) {
					$hidden[] = $control;
				} else {
					$rows[] = $control;
				}
			}
			$elements[] = $control;
		}

		$definition = $form->getDefinition();
		$required = [];
		foreach ($form->getRequired() as $element) {
			$required[] = $element->getName(false);
		}

		return $extra + [
			'name' => $form->getName(),
			'title' => $form->getTitle(),
			'action' => $form->getAction(),
			'method' => $form->getMethod(),
			'extra' => $form->getExtra(),
			'elements' => $elements,
			'rows' => $rows,
			'hidden' => $hidden,
			'validation' => [
				'required' => $required,
				'javascript' => $form->renderValidationJS(false),
			],
			'definition' => $definition->toArray(),
		];
	}

	/**
	 * Is the field a section heading (or the end of one)?
	 */
	public static function isSection(Field $field): bool {
		return $field->type === Field::TYPE_SECTION || $field->type === Field::TYPE_SECTION_CLOSE;
	}
}
