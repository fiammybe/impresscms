<?php

declare(strict_types=1);

namespace App\Ipf\Form;

use Icms\Capability\Definitions;
use Icms\Capability\Integration;
use Icms\Capability\Registry;
use Icms\Form\Element;
use Icms\Form\Elements\Hidden;
use Icms\Form\Elements\Select;
use Icms\Form\Elements\Text;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * The capability sections of IPF forms in framework-handled forms (design
 * D14): the fields the active providers contribute to the legacy IPF form,
 * under the same names (`capability[tagging][tags]`), in an unmapped group
 * `capability`. App\Ipf\IpfPersister hands the bound group to the providers.
 *
 * Text, select and hidden elements are supported; a provider's other elements
 * are left out (and logged).
 */
final class CapabilityFormExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [IpfEntityType::class];
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $sections = Integration::instance()->formSections($options['data'] ?? null);

        if ($sections === []) {
            return;
        }

        $group = $builder->create(Integration::FIELD, FormType::class, ['mapped' => false, 'required' => false, 'label' => false]);

        foreach ($sections as $capability => $elements) {
            $section = $builder->create($capability, FormType::class, ['label' => Definitions::title($capability), 'required' => false]);

            foreach ($elements as $element) {
                $field = self::field($element, Integration::prefix($capability));

                if ($field === null) {
                    Registry::log(sprintf('The %s field "%s" (%s) has no framework form field.', $capability, $element->getName(false), $element::class));

                    continue;
                }

                [$name, $type, $fieldOptions] = $field;
                $section->add($name, $type, $fieldOptions);
            }

            $group->add($section);
        }

        $builder->add($group);
    }

    /**
     * The framework field for a provider's element, named by the part of its
     * name after the prefix ("capability[tagging][tags]" → "tags").
     *
     * @return array{0: string, 1: class-string, 2: array<string, mixed>}|null
     */
    public static function field(Element $element, string $prefix): ?array
    {
        if (!preg_match('/^' . preg_quote($prefix, '/') . '\[([^\[\]]+)\]$/', $element->getName(false), $matches)) {
            return null;
        }

        $options = [
            'label' => strip_tags($element->getCaption()),
            'help' => $element->getDescription() ?: null,
            'required' => false,
        ];

        if ($element instanceof Select) {
            $choices = $element->getOptions();
            $values = $element->getValue();

            return [$matches[1], ChoiceType::class, $options + [
                'choices' => array_map('strval', array_keys($choices)),
                'choice_label' => static fn (string $value): string => (string) $choices[$value],
                'multiple' => $element->isMultiple(),
                'placeholder' => false,
                'data' => $element->isMultiple() ? $values : ($values[0] ?? null),
            ]];
        }

        if ($element instanceof Hidden) {
            return [$matches[1], HiddenType::class, ['data' => (string) $element->getValue()]];
        }

        if ($element instanceof Text) {
            return [$matches[1], TextType::class, $options + ['data' => (string) $element->getValue()]];
        }

        return null;
    }
}
