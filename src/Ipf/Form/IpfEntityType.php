<?php

declare(strict_types=1);

namespace App\Ipf\Form;

use Icms\Form\Definition\Field;
use Icms\Ipf\Form\DefinitionBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A form built from the metadata of an IPF entity (design D7): one field per
 * variable, with the visibility rules of Icms\Ipf\Form\Base::createElements(),
 * plus one group selection per permission of the entity's handler
 * (createPermissionControls()).
 *
 * - the fields are those of Icms\Ipf\Form\DefinitionBuilder, which the
 *   legacy form creates its elements from
 * - the key field and variables not shown on the form are hidden values; the
 *   key is not mapped, the entity is loaded by it
 * - other variables get the field ControlMap chooses, labelled with
 *   `form_caption` and described with `form_dsc`
 * - each permission is a multiple choice of all groups, named after the
 *   permission, not mapped (App\Ipf\IpfPersister stores it)
 *
 * The field names are the variable names, so with an unnamed root form
 * (IpfFormFactory) the legacy IPF form's HTML submits to it unchanged.
 * Values are mapped by IpfEntityDataMapper.
 */
final class IpfEntityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $entity = $options['data'];
        if (!is_object($entity) || !isset($entity->vars, $entity->handler)) {
            throw new \InvalidArgumentException('An IpfEntityType form needs an IPF entity as its data.');
        }

        foreach (self::fields($entity) as $key => [$type, $fieldOptions]) {
            $builder->add($key, $type, $fieldOptions);
        }
        foreach (self::permissionFields($entity) as $name => $fieldOptions) {
            $builder->add($name, ChoiceType::class, $fieldOptions);
        }

        $builder->setDataMapper(new IpfEntityDataMapper());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'compound' => true,
            'empty_data' => null,
        ]);
    }

    /**
     * The entity's fields, in the order of its variables: the fields of
     * Icms\Ipf\Form\DefinitionBuilder, the rules the legacy IPF form uses.
     * Hidden values are HiddenType (the key is not mapped: the entity is
     * loaded by it); sections are layout and have no field; anything else
     * goes through ControlMap, which rejects what it does not support.
     *
     * @return array<string, array{class-string, array<string, mixed>}>
     */
    public static function fields(object $entity): array
    {
        $fields = [];
        foreach ((new DefinitionBuilder($entity))->fields() as $key => $field) {
            if ($field->hidden) {
                $fields[$key] = [HiddenType::class, ['mapped' => $key !== (string) $entity->handler->keyName]];
            } elseif ($field->type !== Field::TYPE_SECTION && $field->type !== Field::TYPE_SECTION_CLOSE) {
                $fields[$key] = ControlMap::fromDefinition($field);
            }
        }

        return $fields;
    }

    /**
     * The group selections for the handler's permissions, with the groups that
     * hold each permission now (for a new entity: the module's default)
     *
     * @return array<string, array<string, mixed>>
     */
    public static function permissionFields(object $entity): array
    {
        $fields = [];
        foreach ((new DefinitionBuilder($entity))->permissions() as $permission) {
            [, $options] = ControlMap::fromDefinition($permission);
            $fields[$permission->name] = [
                'label' => $options['label'],
                'help' => $options['help'],
                'choices' => $options['choices'],
                'choice_label' => $options['choice_label'],
                'multiple' => true,
                'required' => false,
                'mapped' => false,
                'data' => array_values(array_map('intval', $permission->selected)),
                'invalid_message' => $options['invalid_message'],
            ];
        }

        return $fields;
    }
}
