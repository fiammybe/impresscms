<?php
/**
 * The neutral form definition: value objects and what controls describe.
 *
 * @package icms\tests
 */

declare(strict_types=1);

namespace Icms\Tests\Unit\Form;

use Icms\Form\Definition\Choice;
use Icms\Form\Definition\ChoiceField;
use Icms\Form\Definition\CompoundField;
use Icms\Form\Definition\Field;
use Icms\Form\Definition\Form;
use Icms\Form\Elements\Hidden;
use Icms\Form\Elements\Select;
use Icms\Form\Elements\Textarea;
use Icms\Form\View\FormView;
use Icms\Tests\Support\FakeIpfEntity;
use Icms\Tests\Support\TemplateEnvironment;
use PHPUnit\Framework\TestCase;

final class DefinitionTest extends TestCase
{
    protected function setUp(): void
    {
        TemplateEnvironment::boot();
    }

    public function testDefinitionClassesDependOnNoRenderer(): void
    {
        foreach (glob(dirname(__DIR__, 3) . '/libraries/Icms/Form/Definition/*.php') ?: [] as $file) {
            $source = '';
            foreach (token_get_all((string) file_get_contents($file)) as $token) {
                if (!is_array($token) || !in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true)) {
                    $source .= is_array($token) ? $token[1] : $token;
                }
            }
            foreach (['Smarty', 'Symfony', 'Icms\\View', 'Tpl', '<input', '<select', 'render('] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $source, basename($file) . " mentions $forbidden");
            }
        }
    }

    public function testChoiceFieldGroupsConsecutiveOptions(): void
    {
        $field = new ChoiceField('visiblein', 'page', options: [
            new Choice('0-1', 'Top'),
            new Choice('1-0', 'All', 'System'),
            new Choice('1-2', 'Admin', 'System'),
            new Choice('3-0', 'All', 'News'),
        ], selected: ['1-2'], multiple: true, size: 10);

        $array = $field->toArray();
        self::assertSame(['1-2'], $array['value']);
        self::assertSame(['', 'System', 'News'], array_column($array['groups'], 'label'));
        self::assertSame(['1-0', '1-2'], array_column($array['groups'][1]['options'], 'value'));
        self::assertTrue($array['groups'][1]['options'][1]['selected']);
        self::assertFalse($array['options'][0]['selected']);
        self::assertSame(['0-1' => 'Top', '1-0' => 'All', '1-2' => 'Admin', '3-0' => 'All'], $field->labels());
    }

    public function testSingleChoiceValueIsTheSelectedValue(): void
    {
        self::assertSame('b', (new ChoiceField('x', 'select', options: Choice::fromArray(['a' => 'A', 'b' => 'B']), selected: ['b']))->value);
        self::assertNull((new ChoiceField('x', 'select'))->value);
    }

    public function testWithKeepsTheClassAndOtherProperties(): void
    {
        $field = new ChoiceField('x', 'select', 'X', options: Choice::fromArray(['a' => 'A']), selected: ['a'], size: 3);
        $copy = $field->with(required: true);

        self::assertInstanceOf(ChoiceField::class, $copy);
        self::assertTrue($copy->required);
        self::assertSame(3, $copy->size);
        self::assertSame('X', $copy->label);
        self::assertFalse($field->required);
    }

    public function testFormListsFieldsAndChildrenByName(): void
    {
        $form = new Form('f', rows: [
            new Field('title', 'text'),
            new CompoundField('image', 'image', children: ['upload' => new Field('upload_image', 'file')]),
        ], hidden: [new Field('id', 'hidden', hidden: true)]);

        self::assertSame(['title', 'image', 'upload_image', 'id'], array_keys($form->fields()));
        self::assertSame(['title', 'image'], array_column($form->toArray()['rows'], 'name'));
    }

    public function testSelectDescribesItsChoices(): void
    {
        $select = new Select('Fruit', 'fruit', ['a', 'c'], 4, true);
        $select->addOptionArray(['a' => 'Apple', 'b' => 'Banana', 'c' => 'Cherry']);
        $select->setExtra('disabled="disabled"');
        $select->setDescription('Pick some');

        $field = $select->getDefinition();
        self::assertInstanceOf(ChoiceField::class, $field);
        self::assertSame('select', $field->type);
        self::assertSame(['a', 'b', 'c'], array_map(static fn (Choice $c): string => $c->value, $field->options));
        self::assertSame(['a', 'c'], $field->selected);
        self::assertTrue($field->multiple);
        self::assertSame(4, $field->size);
        self::assertTrue($field->disabled);
        self::assertSame('Pick some', $field->description);
        self::assertSame('icms_form_elements_select_display.html', $select->getDefinitionTemplate());
    }

    public function testCustomTemplateReplacesTheDefinitionTemplate(): void
    {
        $textarea = new Textarea('Body', 'body', 'x', 3, 20);
        $textarea->customTemplate = 'mymodule_textarea.html';

        self::assertSame('mymodule_textarea.html', $textarea->getDefinitionTemplate());
        self::assertSame(['id' => 'body_tarea', 'rows' => 3, 'cols' => 20], $textarea->getDefinition()->attributes);
    }

    public function testSubclassThatRendersItselfIsLegacy(): void
    {
        $select = new class ('Mine', 'mine') extends Select {
            public function render(): string
            {
                return '<b>own markup</b>';
            }
        };

        $field = $select->getDefinition();
        self::assertTrue($field->isLegacy());
        self::assertSame('mine', $field->name);
        self::assertSame($select::class, $field->attribute('class'));
        self::assertNull($select->getDefinitionTemplate());

        $control = FormView::control($select);
        self::assertNull($control['template']);
        self::assertSame('<b>own markup</b>', $control['html']);
    }

    public function testSubclassOptsInByDescribingWhatItRenders(): void
    {
        $select = new class ('Mine', 'mine') extends Select {
            public function render(): string
            {
                return '<b>own markup</b>';
            }

            protected function describe(): Field
            {
                return parent::describe()->with(type: 'colour');
            }
        };

        self::assertSame('colour', $select->getDefinition()->type);
        // it renders itself: layouts use its HTML, not the select template
        self::assertNull($select->getDefinitionTemplate());
    }

    public function testControlWithoutDefinitionIsLegacy(): void
    {
        $element = new class extends \Icms\Form\Element {
            public function __construct()
            {
                $this->setName('custom');
                $this->setCaption('Custom');
            }

            public function render(): string
            {
                return '<i>custom</i>';
            }
        };

        $field = $element->getDefinition();
        self::assertSame(Field::TYPE_LEGACY, $field->type);
        self::assertSame('Custom', $field->label);
    }

    public function testFormViewIncludesDefinitionTemplatesAndLegacyHtml(): void
    {
        $form = new \Icms\Form\Theme('T', 't', 'a.php');
        $form->addElement(new Hidden('op', 'save'));
        $form->addElement(new Textarea('Body', 'body', 'x'));
        $form->addElement(new \Icms\Form\Elements\Text('Name', 'name', 10, 20, 'n'), true);
        $form->addElement('<tr><td>raw</td></tr>');

        $view = FormView::form($form);
        self::assertSame(['op'], array_column(array_column($view['hidden'], 'field'), 'name'));
        self::assertSame(['textarea', 'text', 'raw'], array_column($view['rows'], 'type'));
        // the textarea is included with its definition, not as HTML
        self::assertNull($view['rows'][0]['html']);
        self::assertStringEndsWith('icms_form_elements_textarea_display.html', (string) $view['rows'][0]['template']);
        // the text control's released template has its own variables: its HTML is used
        self::assertNull($view['rows'][1]['template']);
        self::assertStringContainsString('name="name"', (string) $view['rows'][1]['html']);
        self::assertSame(['name'], $view['validation']['required']);
        self::assertStringContainsString('xoopsFormValidate_t', $view['validation']['javascript']);
        self::assertSame(['body', 'name'], array_column($view['definition']['rows'], 'name'));
    }

    public function testFormTemplateDoesNotNeedPrerenderedHtmlForDefinedControls(): void
    {
        $form = new \Icms\Form\Theme('T', 't', 'a.php');
        $select = new Select('Colour', 'colour', 'b');
        $select->addOptionArray(['a' => 'A', 'b' => 'B']);
        $form->addElement($select);
        $form->customTemplate = 'file:' . self::template('<{foreach from=$form.rows item=row}>[<{$row.field.label}>:<{foreach from=$row.field.options item=o}><{$o.value}><{if $o.selected}>*<{/if}><{/foreach}>]<{/foreach}>');

        self::assertSame('[Colour:ab*]', $form->render());
    }

    public function testCaptchaDescribesItsCodeInput(): void
    {
        $captcha = new \Icms\Form\Elements\Captcha\Image();
        $captcha->loadConfig(['name' => 'scode']);
        $field = $captcha->getDefinition();

        self::assertSame('captcha', $field->type);
        self::assertSame('image', $field->attribute('mode'));
        self::assertSame(['maxlength' => 4], $field->child('code')?->constraints);
    }

    public function testIpfImageIsCompound(): void
    {
        $entity = new FakeIpfEntity(['image' => ['value' => 'pic.png', 'data_type' => XOBJ_DTYPE_IMAGE, 'form_caption' => 'Image']]);
        $field = (new \Icms\Ipf\Form\Elements\Image($entity, 'image'))->getDefinition();

        self::assertInstanceOf(CompoundField::class, $field);
        self::assertSame('image', $field->name);
        self::assertSame(['upload', 'url', 'delete'], array_keys($field->children));
        self::assertSame('upload_image', $field->child('upload')?->name);
        self::assertSame('file', $field->child('upload')?->type);
        self::assertSame('local', $field->attribute('image_type'));
        self::assertSame('checkbox', $field->child('delete')?->type);
    }

    private static function template(string $source): string
    {
        $file = sys_get_temp_dir() . '/icms-definition-test-' . md5($source) . '.html';
        file_put_contents($file, $source);

        return $file;
    }
}
