<?php

declare(strict_types=1);

namespace App\Ipf\Compiler;

use App\Ipf\Definition\DataType;
use App\Ipf\Definition\FieldDefinition;
use App\Ipf\Definition\ResourceDefinition;

/**
 * Laravel validation rules for the fields of the legacy IPF form, with the
 * behaviour of Icms\Ipf\Controller::postDataToObject():
 *
 * | field                                   | rules                               |
 * |-----------------------------------------|-------------------------------------|
 * | the key, a read-only field              | none: never written from the form   |
 * | hidden (not displayed on the form)      | sometimes                           |
 * | control `text`, data type TXTBOX        | string, max:<maxlength>             |
 * | control `text`, INT                     | integer                             |
 * | control `text`, FLOAT or CURRENCY       | numeric                             |
 * | control `textarea`                      | string                              |
 * | control `select`                        | ipf_choice:<resource>,<field>       |
 * | control `selectmulti`                   | array, each ipf_choice              |
 * | a handler permission                    | nullable, array, each ipf_group     |
 * | required variable                       | required, otherwise nullable        |
 *
 * Every field is `sometimes`: only the submitted keys are validated and
 * written. Messages are templates resolved at runtime with the field's
 * caption: a legacy language constant (_XOBJ_ERR_REQUIRED, …) or a sprintf
 * format. Any other control is reported as unsupported, never coerced.
 */
final class RuleCompiler
{
    private const INVALID_CHOICE = '%s: the selected choice is invalid.';

    /**
     * @return array{
     *     rules: array<string, list<string>>,
     *     messages: array<string, list<string|int>>,
     *     unsupported: array<string, string>
     * } unsupported: field => control
     */
    public function compile(ResourceDefinition $definition): array
    {
        $rules = [];
        $messages = [];
        $unsupported = [];

        foreach ($definition->fields as $name => $field) {
            if ($field->isKey || $field->readonly || ! $field->onForm()) {
                continue;
            }

            if ($field->isHidden()) {
                $rules[$name] = ['sometimes'];

                continue;
            }

            $typed = $this->typed($definition, $field);
            if ($typed === null) {
                $unsupported[$name] = (string) $field->control?->name;

                continue;
            }

            $rules[$name] = ['sometimes', $field->required ? 'required' : 'nullable', ...$typed['rules']];
            if ($field->required) {
                $messages["{$name}.required"] = ['_XOBJ_ERR_REQUIRED', $name];
            }

            foreach ($typed['messages'] as $rule => $message) {
                $messages["{$name}.{$rule}"] = $message;
            }

            if (isset($typed['items'])) {
                $rules["{$name}.*"] = $typed['items'];
                $messages["{$name}.*.ipf_choice"] = [self::INVALID_CHOICE, $name];
            }
        }

        foreach ($definition->permissions as $permission) {
            $rules[$permission->name] = ['sometimes', 'nullable', 'array'];
            $rules["{$permission->name}.*"] = ['ipf_group'];
            $messages["{$permission->name}.array"] = [self::INVALID_CHOICE, $permission->name];
            $messages["{$permission->name}.*.ipf_group"] = [self::INVALID_CHOICE, $permission->name];
        }

        return ['rules' => $rules, 'messages' => $messages, 'unsupported' => $unsupported];
    }

    /**
     * @return array{
     *     rules: list<string>,
     *     messages: array<string, list<string|int>>,
     *     items?: list<string>
     * }|null
     */
    private function typed(ResourceDefinition $definition, FieldDefinition $field): ?array
    {
        $name = $field->name;
        $choice = "ipf_choice:{$definition->key()},{$name}";

        return match ($field->control?->name) {
            'text' => match ($field->type) {
                DataType::INT => ['rules' => ['integer'], 'messages' => ['integer' => ['%s: please enter a whole number.', $name]]],
                DataType::FLOAT, DataType::CURRENCY => ['rules' => ['numeric'], 'messages' => ['numeric' => ['%s: please enter a number.', $name]]],
                DataType::TXTBOX => $this->text($field),
                default => null,
            },
            'textarea' => ['rules' => ['string'], 'messages' => ['string' => ['%s: please enter text.', $name]]],
            'select' => $field->control->options === null ? null : ['rules' => [$choice], 'messages' => ['ipf_choice' => [self::INVALID_CHOICE, $name]]],
            'selectmulti' => $field->control->options === null ? null : ['rules' => ['array'], 'messages' => ['array' => [self::INVALID_CHOICE, $name]], 'items' => [$choice]],
            default => null,
        };
    }

    /**
     * @return array{
     *     rules: list<string>,
     *     messages: array<string, list<string|int>>
     * }
     */
    private function text(FieldDefinition $field): array
    {
        $rules = ['string'];
        $messages = ['string' => ['%s: please enter text.', $field->name]];
        if ($field->maxLength !== null) {
            $rules[] = "max:{$field->maxLength}";
            $messages['max'] = ['_XOBJ_ERR_SHORTERTHAN', $field->name, $field->maxLength];
        }

        return ['rules' => $rules, 'messages' => $messages];
    }
}
