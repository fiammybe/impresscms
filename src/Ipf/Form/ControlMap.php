<?php

declare(strict_types=1);

namespace App\Ipf\Form;

use Icms\Form\Definition\ChoiceField;
use Icms\Form\Definition\Field;
use Icms\Ipf\Form\DefinitionBuilder;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Translates a field definition (Icms\Form\Definition, built for IPF
 * entities by Icms\Ipf\Form\DefinitionBuilder, the rules of the legacy IPF
 * form) into a Symfony form field (design D7):
 *
 * | definition                                 | field                 | constraints        |
 * |--------------------------------------------|-----------------------|--------------------|
 * | text                                       | TextType              | Length(maxlength)  |
 * | text with constraint integer               | IntegerType           | an integer         |
 * | textarea                                   | TextareaType          | –                  |
 * | select (ChoiceField), multiple or not      | ChoiceType (multiple) | one of the choices |
 * | required                                   | –                     | NotBlank           |
 *
 * Messages name the field by its caption, in the words of the legacy
 * validation (_XOBJ_ERR_REQUIRED, _XOBJ_ERR_SHORTERTHAN). Any other field
 * (legacy controls, numbers, compound fields) raises
 * UnsupportedIpfControlException: never a silent skip.
 */
final class ControlMap
{
    /**
     * The field type and options for a definition
     *
     * @return array{class-string, array<string, mixed>}
     */
    public static function fromDefinition(Field $field): array
    {
        $caption = self::caption($field->name, ['form_caption' => $field->label]);

        [$type, $options] = match (true) {
            $field->type === 'text' && !empty($field->constraints['integer']) => self::integer($caption),
            $field->type === 'text' && empty($field->constraints['number']) => self::text($field, $caption),
            $field->type === 'textarea' => [TextareaType::class, []],
            $field->type === 'select' && $field instanceof ChoiceField => self::choice($field, $caption),
            default => throw new UnsupportedIpfControlException($field->name, self::controlName($field)),
        };

        $constraints = $options['constraints'] ?? [];
        if ($field->required) {
            $constraints[] = new NotBlank(message: self::message(_XOBJ_ERR_REQUIRED, $caption));
        }

        return [$type, [
            'label' => $caption,
            'help' => $field->description !== '' ? $field->description : null,
            'required' => $field->required,
            'disabled' => $field->disabled,
        ] + ['constraints' => $constraints] + $options];
    }

    /**
     * The field type and options for a variable shown on the form
     *
     * @param array<string, mixed>      $var     the variable definition (Entity::$vars[$key])
     * @param array<string, mixed>|null $control the variable's control, if any (as on the entity)
     *
     * @return array{class-string, array<string, mixed>}
     */
    public static function field(object $entity, string $key, array $var, ?array $control): array
    {
        $definition = (new DefinitionBuilder($entity))->fields()[$key] ?? null;
        if ($definition === null) {
            throw new UnsupportedIpfControlException($key, (string) ($control['name'] ?? ''));
        }

        return self::fromDefinition($definition);
    }

    /**
     * The options of a select control, in the order the legacy select list
     * shows them (Icms\Ipf\Form\DefinitionBuilder::options())
     *
     * @param array<string, mixed> $control
     *
     * @return array<int|string, string> value => label
     */
    public static function options(object $entity, array $control): array
    {
        return (new DefinitionBuilder($entity))->options($control);
    }

    /**
     * The caption the legacy form shows for a variable, as plain text.
     *
     * @param array<string, mixed> $var
     */
    public static function caption(string $key, array $var): string
    {
        $caption = trim(html_entity_decode(strip_tags((string) ($var['form_caption'] ?? '')), ENT_QUOTES, 'UTF-8'));

        return $caption !== '' ? $caption : $key;
    }

    /**
     * A message in the legacy wording, naming the caption.
     */
    public static function message(string $format, string $caption, int ...$arguments): string
    {
        // the validator fills {{ placeholders }}; the caption is final text
        return str_replace('{{', '{ {', sprintf($format, $caption, ...$arguments));
    }

    /**
     * @return array{class-string, array<string, mixed>}
     */
    private static function text(Field $field, string $caption): array
    {
        $constraints = [];
        $maxlength = (int) ($field->constraints['maxlength'] ?? 0);
        if ($maxlength > 0) {
            $constraints[] = new Length(max: $maxlength, maxMessage: self::message(_XOBJ_ERR_SHORTERTHAN, $caption, $maxlength));
        }

        // legacy trims when it validates, but stores what was posted
        return [TextType::class, ['constraints' => $constraints, 'trim' => false]];
    }

    /**
     * @return array{class-string, array<string, mixed>}
     */
    private static function integer(string $caption): array
    {
        return [IntegerType::class, ['invalid_message' => $caption . ': please enter a whole number.']];
    }

    /**
     * @return array{class-string, array<string, mixed>}
     */
    private static function choice(ChoiceField $field, string $caption): array
    {
        // keys that look like integers are integers, as in the legacy option arrays
        $labels = $field->labels();

        return [ChoiceType::class, [
            'choices' => array_keys($labels),
            'choice_label' => static fn (int|string $value): string => (string) $labels[$value],
            'multiple' => $field->multiple,
            'expanded' => false,
            // ChoiceType rejects values outside the choices with this message
            'invalid_message' => $caption . ': the selected choice is invalid.',
        ]];
    }

    /**
     * The control a definition stands for, for the exception
     */
    private static function controlName(Field $field): string
    {
        if ($field->isLegacy()) {
            $control = (string) $field->attribute('control', '');

            return $control !== '' ? $control : self::dataTypeName((int) $field->attribute('data_type', 0));
        }

        return $field->type;
    }

    private static function dataTypeName(int $type): string
    {
        foreach (get_defined_constants() as $name => $value) {
            if (str_starts_with((string) $name, 'XOBJ_DTYPE_') && $value === $type) {
                return $name;
            }
        }

        return 'data type ' . $type;
    }
}
