<?php
/**
 * Named renderings of form controls and layouts, for the characterization
 * snapshots of the form output.
 *
 * Uses only the public API that existed before the form templates (the
 * constructors, addElement(), insertBreak(), render(), assign()), so that the
 * scenarios run against an older checkout too:
 *
 *     php htdocs/tests/Support/capture-form-output.php > snapshot.json
 *
 * @package icms\tests
 */

declare(strict_types=1);

namespace Icms\Tests\Support;

final class FormOutputScenarios
{
    /**
     * @return array<string, \Closure(): string>
     */
    public static function all(): array
    {
        TemplateEnvironment::boot();
        TemplateEnvironment::setHandler('icms_member', new class {
            /** @return array<int, string> */
            public function getGroupList(): array
            {
                return [2 => 'Registered Users', 1 => 'Webmasters', 3 => 'Anonymous & <guests>'];
            }
        });

        return [
            'select' => static function (): string {
                $select = new \Icms\Form\Elements\Select('Colour', 'colour', 'b');
                $select->addOptionArray(['a' => 'Apple', 'b' => 'Banana', 'c&d' => 'C <i>&amp;</i> "D"']);

                return $select->render();
            },
            'select-multiple' => static function (): string {
                $select = new \Icms\Form\Elements\Select('Fruit', 'fruit', ['a', 'c&d'], 4, true);
                $select->addOptionArray(['a' => 'Apple', 'b' => 'Banana', 'c&d' => 'C & D']);
                $select->setExtra('onchange="go(this)"');

                return $select->render();
            },
            'select-empty' => static function (): string {
                return (new \Icms\Form\Elements\Select('Nothing', 'nothing'))->render();
            },
            'textarea' => static function (): string {
                $textarea = new \Icms\Form\Elements\Textarea('Body', 'body', 'Some &amp; <b>text</b>', 5, 40);
                $textarea->setExtra('class="big"');

                return $textarea->render();
            },
            'hidden' => static function (): string {
                return (new \Icms\Form\Elements\Hidden('op', 'save'))->render();
            },
            'hidden-array' => static function (): string {
                return (new \Icms\Form\Elements\Hidden('ids', [3, 5]))->render();
            },
            'groupperm' => static function (): string {
                $perm = new \Icms\Form\Elements\Groupperm('Access', 'perms', 2, [1, 3, 6]);
                $perm->setOptionTree(self::optionTree());

                return $perm->render();
            },
            'captcha-image' => static function (): string {
                $captcha = new \Icms\Form\Elements\Captcha\Image();
                $captcha->loadConfig(['name' => 'scode']);

                return $captcha->render();
            },
            'captcha-text' => static function (): string {
                mt_srand(7);
                $captcha = new \Icms\Form\Elements\Captcha\Text();
                $captcha->loadConfig(['name' => 'scode']);

                return $captcha->render();
            },
            'ipf-file' => static function (): string {
                return (new \Icms\Ipf\Form\Elements\File(self::fileEntity('manual.pdf'), 'doc'))->render();
            },
            'ipf-file-empty' => static function (): string {
                return (new \Icms\Ipf\Form\Elements\File(self::fileEntity(''), 'doc'))->render();
            },
            'ipf-image-local' => static function (): string {
                return (new \Icms\Ipf\Form\Elements\Image(self::imageEntity('pic.png', false), 'image'))->render();
            },
            'ipf-image-external-nourl' => static function (): string {
                $entity = self::imageEntity('http://example.com/a.png', true);
                $entity->controls['image'] = ['nourl' => true];

                return (new \Icms\Ipf\Form\Elements\Image($entity, 'image'))->render();
            },
            'ipf-image-new' => static function (): string {
                return (new \Icms\Ipf\Form\Elements\Image(self::imageEntity('', true), 'image'))->render();
            },
            'form-theme' => static fn (): string => self::layout(new \Icms\Form\Theme('Theme <em>form</em>', 'themed', 'save.php'))->render(),
            'form-table' => static fn (): string => self::layout(new \Icms\Form\Table('Table form', 'tabled', 'save.php', 'get'))->render(),
            'form-simple' => static fn (): string => self::layout(new \Icms\Form\Simple('Simple form', 'simple', 'save.php'))->render(),
            'form-theme-assign' => static function (): string {
                $tpl = self::recorder();
                self::layout(new \Icms\Form\Theme('Theme form', 'themed', 'save.php'))->assign($tpl);

                return self::export($tpl->vars);
            },
            'pagenav-text' => static fn (): string => self::captured(static fn () => (new \Icms\View\PageNav(95, 10, 40, 'start', 'cat=3'))->renderNav()),
            'pagenav-text-first' => static fn (): string => self::captured(static fn () => (new \Icms\View\PageNav(25, 10, 0))->renderNav(2)),
            'pagenav-select' => static fn (): string => self::captured(static fn () => (new \Icms\View\PageNav(95, 10, 40, 'start', 'cat=3'))->renderSelect(true)),
            'pagenav-image' => static fn (): string => self::captured(static fn () => (new \Icms\View\PageNav(95, 10, 40))->renderImageNav()),
            'ipf-form' => static fn (): string => self::ipfForm(false)->render(),
            'ipf-form-new' => static fn (): string => self::ipfForm(true)->render(),
            'ipf-form-assign' => static function (): string {
                $tpl = self::recorder();
                self::ipfForm(false)->assign($tpl);

                return self::export($tpl->vars);
            },
        ];
    }

    /**
     * The entity of the IPF form scenarios
     */
    public static function entity(bool $new): FakeIpfEntity
    {
        return new FakeIpfEntity(
            [
                'id' => ['value' => 7, 'data_type' => XOBJ_DTYPE_INT],
                'title' => ['value' => 'A "quoted" & <b>bold</b> title', 'required' => true, 'maxlength' => 100, 'form_caption' => 'Title', 'form_dsc' => 'The <i>title</i>'],
                'weight' => ['value' => 3, 'data_type' => XOBJ_DTYPE_INT, 'form_caption' => 'Weight'],
                'details' => ['value' => 0, 'data_type' => XOBJ_DTYPE_FORM_SECTION, 'form_caption' => 'Details'],
                'body' => ['value' => "Line 1\nLine <2>", 'data_type' => XOBJ_DTYPE_TXTAREA, 'form_caption' => 'Body', 'form_dsc' => 'Long text'],
                'colour' => ['value' => 'green', 'required' => true, 'form_caption' => 'Colour'],
                'shades' => ['value' => ['red'], 'data_type' => XOBJ_DTYPE_ARRAY, 'form_caption' => 'Shades'],
                'details_close' => ['value' => 0, 'data_type' => XOBJ_DTYPE_FORM_SECTION_CLOSE],
                'code' => ['value' => 'X-1', 'readonly' => true, 'form_caption' => 'Code'],
                'secret' => ['value' => 'hush', 'displayOnForm' => false],
                'tags' => ['value' => [], 'data_type' => XOBJ_DTYPE_ARRAY],
            ],
            [
                'colour' => ['options' => ['red' => 'Red', 'green' => 'Green', 'blue' => 'Blue & co']],
                'shades' => ['name' => 'selectmulti', 'method' => 'getColours'],
            ],
            $new,
            [['perm_name' => 'item_view', 'caption' => 'Viewers', 'description' => 'Groups that see the item']],
            ['def_perm_item_view' => [1, 2]],
            ['item_view' => [2, 3]],
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function optionTree(): array
    {
        $tree = [0 => ['children' => [1, 4, 5, 6, 7]]];
        $tree[1] = ['id' => 1, 'name' => 'Alpha', 'children' => [2], 'allchild' => [2, 3]];
        $tree[2] = ['id' => 2, 'name' => 'Beta & "co"', 'children' => [3], 'allchild' => [3]];
        $tree[3] = ['id' => 3, 'name' => 'Gamma', 'allchild' => []];
        foreach ([4 => 'Delta', 5 => 'Epsilon', 6 => 'Zeta', 7 => 'Eta'] as $id => $name) {
            $tree[$id] = ['id' => $id, 'name' => $name, 'allchild' => []];
        }

        return $tree;
    }

    private static function fileEntity(string $file): FakeIpfEntity
    {
        return new FakeIpfEntity(['doc' => ['value' => $file, 'data_type' => XOBJ_DTYPE_FILE, 'form_caption' => 'Document', 'form_maxfilesize' => 2048]]);
    }

    private static function imageEntity(string $image, bool $new): FakeIpfEntity
    {
        return new FakeIpfEntity(['image' => ['value' => $image, 'data_type' => XOBJ_DTYPE_IMAGE, 'form_caption' => 'Image', 'form_maxfilesize' => 4096]], [], $new);
    }

    private static function layout(\Icms\Form\Base $form): \Icms\Form\Base
    {
        $form->addElement(new \Icms\Form\Elements\Text('Name', 'name', 30, 60, 'Jane & "Joe"'), true);
        $select = new \Icms\Form\Elements\Select('Colour', 'colour', 'b');
        $select->addOptionArray(['a' => 'Apple', 'b' => 'Banana']);
        $select->setDescription('Pick <b>one</b>');
        $form->addElement($select);
        $form->addElement(new \Icms\Form\Elements\Textarea('Body', 'body', 'Hello', 3, 30));
        $form->insertBreak('Break <b>here</b>', 'foot');
        $form->addElement(new \Icms\Form\Elements\Label('Note', 'Just a <i>label</i>'));
        $form->addElement(new \Icms\Form\Elements\Hidden('op', 'save'));
        $form->insertBreak();
        $form->addElement(new \Icms\Form\Elements\Hidden('id', 9));
        $form->addElement(new \Icms\Form\Elements\Button('', 'submit', 'Send', 'submit'));

        return $form;
    }

    private static function ipfForm(bool $new): \Icms\Ipf\Form\Base
    {
        $entity = self::entity($new);

        return new \Icms\Ipf\Form\Base($entity, 'itemform', 'Edit item', 'item.php');
    }

    /**
     * What a rendering returns, after what it prints
     */
    private static function captured(\Closure $render): string
    {
        ob_start();
        try {
            $returned = (string) $render();
        } finally {
            $printed = (string) ob_get_clean();
        }

        return $printed . $returned;
    }

    /**
     * A template stand-in that records what is assigned
     */
    private static function recorder(): object
    {
        return new class {
            /** @var array<string, mixed> */
            public array $vars = [];

            public function assign(string|array $name, mixed $value = null): void
            {
                if (is_array($name)) {
                    $this->vars = array_merge($this->vars, $name);
                } else {
                    $this->vars[$name] = $value;
                }
            }
        };
    }

    /**
     * @param array<string, mixed> $vars
     */
    private static function export(array $vars): string
    {
        return (string) json_encode($vars, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
