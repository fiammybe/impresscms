<?php
/**
 * Icms\Ipf\Form\DefinitionBuilder: IPF metadata to a form definition, and
 * parity with the elements of the legacy IPF form.
 *
 * @package icms\tests
 */

declare(strict_types=1);

namespace Icms\Tests\Unit\Ipf;

use Icms\Form\Definition\ChoiceField;
use Icms\Form\Definition\CompoundField;
use Icms\Form\Definition\Field;
use Icms\Ipf\Form\DefinitionBuilder;
use Icms\Tests\Support\FakeIpfEntity;
use Icms\Tests\Support\FormOutputScenarios;
use Icms\Tests\Support\TemplateEnvironment;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('spec:ipf-forms')]
final class DefinitionBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        FormOutputScenarios::all();
    }

    public function testPlanFollowsTheLegacyRules(): void
    {
        $plan = (new DefinitionBuilder(FormOutputScenarios::entity(false)))->plan();

        self::assertSame(DefinitionBuilder::STEP_HIDDEN, $plan['id']['step'], 'the key');
        self::assertSame(DefinitionBuilder::STEP_HIDDEN, $plan['secret']['step'], 'not on the form');
        self::assertSame(['step' => 'control', 'control' => 'text', 'set_control' => ['name' => 'text', 'size' => '5']], $plan['weight']);
        self::assertSame('select', $plan['colour']['control'], 'a control without a name');
        self::assertSame('selectmulti', $plan['shades']['control']);
        self::assertSame(['close' => true], $plan['details_close']['set_control']);
        self::assertSame(DefinitionBuilder::STEP_SKIP, $plan['tags']['step'], 'an array without a control');
    }

    public function testFieldsDescribeTheVariables(): void
    {
        $fields = (new DefinitionBuilder(FormOutputScenarios::entity(false)))->fields();

        self::assertSame(['id', 'title', 'weight', 'details', 'body', 'colour', 'shades', 'details_close', 'code', 'secret'], array_keys($fields));

        self::assertTrue($fields['id']->hidden);
        self::assertSame(7, $fields['id']->value);

        $title = $fields['title'];
        self::assertSame(['text', 'Title', 'The <i>title</i>', true], [$title->type, $title->label, $title->description, $title->required]);
        self::assertSame(['maxlength' => 100], $title->constraints);
        self::assertSame('A &quot;quoted&quot; &amp; &lt;b&gt;bold&lt;/b&gt; title', $title->value, 'the form value, as the legacy control holds it');

        self::assertSame(['integer' => true], $fields['weight']->constraints);
        self::assertSame(5, $fields['weight']->attribute('size'));

        self::assertSame(['textarea', 5, 60], [$fields['body']->type, $fields['body']->attribute('rows'), $fields['body']->attribute('cols')]);

        $colour = $fields['colour'];
        self::assertInstanceOf(ChoiceField::class, $colour);
        self::assertSame(['red' => 'Red', 'green' => 'Green', 'blue' => 'Blue & co'], $colour->labels());
        self::assertSame(['green'], $colour->selected);
        self::assertFalse($colour->multiple);

        $shades = $fields['shades'];
        self::assertInstanceOf(ChoiceField::class, $shades);
        self::assertTrue($shades->multiple);
        self::assertSame(5, $shades->size);
        self::assertSame(['red' => 'Red', 'green' => 'Green & <b>blue</b>'], $shades->labels(), 'options from a handler method');

        self::assertSame([Field::TYPE_SECTION, '0'], [$fields['details']->type, $fields['details']->label]);
        self::assertSame(Field::TYPE_SECTION_CLOSE, $fields['details_close']->type);

        self::assertTrue($fields['code']->disabled, 'read only');
    }

    public function testControlsWithoutDefinitionAreExplicitlyLegacy(): void
    {
        $entity = new FakeIpfEntity([
            'id' => ['value' => 1, 'data_type' => XOBJ_DTYPE_INT],
            'answer' => ['value' => 1, 'data_type' => XOBJ_DTYPE_INT],
            'mail' => ['value' => 'a@b.c', 'data_type' => XOBJ_DTYPE_EMAIL],
        ], ['answer' => ['name' => 'yesno']]);
        $fields = (new DefinitionBuilder($entity))->fields();

        self::assertTrue($fields['answer']->isLegacy());
        self::assertSame('yesno', $fields['answer']->attribute('control'));
        self::assertTrue($fields['mail']->isLegacy(), 'no default control for e-mail addresses');
        self::assertSame('', $fields['mail']->attribute('control'));
    }

    #[DataProvider('newAndExisting')]
    public function testPermissionsAreGroupSelections(bool $new, array $selected): void
    {
        [$permission] = (new DefinitionBuilder(FormOutputScenarios::entity($new)))->permissions();

        self::assertSame(['item_view', 'Viewers', 'Groups that see the item'], [$permission->name, $permission->label, $permission->description]);
        self::assertSame([3 => 'Anonymous & <guests>', 2 => 'Registered Users', 1 => 'Webmasters'], $permission->labels(), 'groups by name');
        self::assertSame($selected, $permission->selected);
        self::assertTrue($permission->multiple);
        self::assertSame(4, $permission->size);
    }

    /**
     * @return array<string, array{bool, list<string>}>
     */
    public static function newAndExisting(): array
    {
        return ['new: module default' => [true, ['1', '2']], 'existing: stored' => [false, ['2', '3']]];
    }

    public function testBuildAddsTheActions(): void
    {
        $form = (new DefinitionBuilder(FormOutputScenarios::entity(true)))->build('itemform', 'Edit item', 'item.php');

        self::assertSame(['id', 'secret'], array_column(array_map(static fn (Field $f): array => ['n' => $f->name], $form->hidden), 'n'));
        [$actions] = $form->actions;
        self::assertInstanceOf(CompoundField::class, $actions);
        self::assertSame(['op', 'create_button', 'cancel_button'], array_keys($actions->children));
        self::assertSame('itemform', $actions->child('op')?->value);
    }

    /**
     * @scenario Legacy elements match the definition
     */
    #[DataProvider('newAndExisting')]
    public function testLegacyFormElementsHaveTheSameDefinitions(bool $new): void
    {
        $entity = FormOutputScenarios::entity($new);
        $built = (new DefinitionBuilder(FormOutputScenarios::entity($new)))->build();
        $legacy = (new \Icms\Ipf\Form\Base($entity, 'itemform', 'Edit item', 'item.php'))->getDefinition();

        $expected = array_merge($built->rows, $built->hidden);
        $actual = [];
        foreach (array_merge($legacy->rows, $legacy->hidden) as $field) {
            // a read only variable is a disabled "<key>-readonly" control plus a hidden value
            if (str_ends_with($field->name, '-readonly')) {
                $field = $field->with(name: substr($field->name, 0, -9));
            } elseif ($field->hidden && isset($actual[$field->name])) {
                continue;
            }
            if ($field->name === 'icms_page_before_form') {
                continue;
            }
            $actual[$field->name] = $field;
        }

        self::assertSame(array_map(static fn (Field $f): string => $f->name, $expected), array_keys($actual));
        foreach ($expected as $field) {
            self::assertSame(self::essentials($field), self::essentials($actual[$field->name]), $field->name);
        }
    }

    /**
     * What both sides must agree on
     *
     * @return array<string, mixed>
     */
    private static function essentials(Field $field): array
    {
        $essentials = [
            'type' => $field->type,
            'label' => $field->label,
            'description' => $field->description,
            'required' => $field->required,
            'disabled' => $field->disabled,
            'hidden' => $field->hidden,
            'value' => $field instanceof ChoiceField ? null : $field->value,
        ];
        foreach (['size', 'maxlength', 'rows', 'cols'] as $attribute) {
            if ($field->attribute($attribute) !== null) {
                $essentials[$attribute] = (int) $field->attribute($attribute);
            }
        }
        if ($field instanceof ChoiceField) {
            $essentials += ['options' => $field->labels(), 'selected' => $field->selected, 'multiple' => $field->multiple, 'size' => $field->size];
        }

        return $essentials;
    }
}
