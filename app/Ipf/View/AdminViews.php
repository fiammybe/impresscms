<?php

declare(strict_types=1);

namespace App\Ipf\View;

use App\Admin\AdminPageRenderer;
use App\Admin\LegacyNavigation;
use App\Ipf\Repository\IpfRecord;
use App\Ipf\Repository\ListPage;
use App\Ipf\Resource\IpfResource;
use App\Ipf\Resource\ResourceDescriptor;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;
use LogicException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin pages of IPF resources with the Blade components of
 * resources/views/components/ipf (`<x-ipf::…>`), inside the ImpressCMS admin
 * theme (AdminPageRenderer).
 *
 * The components receive field descriptors built here from the generated
 * descriptor, the record and the live definition's captions and choices.
 * Forms post the legacy field names to the legacy admin URL, with its `op`
 * and a security token, so they work whichever side owns the URL.
 */
final class AdminViews
{
    public function __construct(
        private readonly AdminPageRenderer $renderer,
        private readonly ViewFactory $views,
        private readonly LegacyNavigation $navigation,
    ) {
    }

    /**
     * A view inside the admin theme.
     *
     * @param array<string, mixed> $data
     */
    public function render(string $view, array $data): Response
    {
        $html = $this->views->make($view, $data)->render();

        return $this->renderer->renderFragment(static function () use ($html): void {
            echo $html;
        });
    }

    /**
     * The form of a record: its fields and permissions in the legacy order.
     */
    public function form(IpfResource $resource, IpfRecord $record, string $caption): HtmlString
    {
        $descriptor = $resource->descriptor;
        $legacy = $this->legacy($descriptor);

        $fields = [];
        foreach ($descriptor::form() as $name) {
            $fields[] = $descriptor::isPermission($name)
                ? $this->permissionField($resource, $record, $name)
                : $this->field($resource, $record, $name);
        }

        return new HtmlString($this->views->make('ipf::admin.form', [
            'id' => 'add' . $descriptor::item(),
            'caption' => $caption,
            'action' => $legacy['script'],
            'fields' => $fields,
            'hidden' => [
                ...$legacy['match'],
                'op' => $legacy['save'],
                'icms_page_before_form' => $this->navigation->pageBeforeForm(),
            ],
            'token' => new HtmlString($this->navigation->tokenField()),
            'submit' => $resource->text('_SUBMIT'),
        ])->render());
    }

    /**
     * The list of a page of records, with quick search, sorting, actions and pages.
     */
    public function list(IpfResource $resource, ListPage $page, Request $request, string $createCaption): HtmlString
    {
        $descriptor = $resource->descriptor;
        $legacy = $this->legacy($descriptor);
        $list = $descriptor::listView() ?? throw new LogicException("{$resource->key()} declares no list (#[ListView]).");
        $key = $descriptor::primaryKey();
        $base = $legacy['script'];

        $columns = [];
        foreach ($list['columns'] as $column) {
            $order = $page->query->sort === $column['field'] && $page->query->order === 'ASC' ? 'DESC' : 'ASC';
            $columns[] = $column + [
                'caption' => $resource->caption($column['field']),
                'url' => $column['sortable'] ? self::url($base, [
                    "{$descriptor::item()}_sortsel" => $column['field'],
                    "{$descriptor::item()}_ordersel" => $order,
                    "quicksearch_{$descriptor::entityClass()}" => $page->query->search,
                    ...$legacy['match'],
                ]) : null,
            ];
        }

        $rows = [];
        foreach ($page->records as $record) {
            $actions = [];
            foreach ($list['actions'] as $action) {
                $op = match ($action) {
                    'edit' => $legacy['form'],
                    'delete' => $legacy['delete'],
                    default => throw new LogicException("Unknown list action \"{$action}\"."),
                };
                $actions[] = [
                    'name' => $action,
                    'label' => $resource->text($action === 'edit' ? '_EDIT' : '_DELETE'),
                    'url' => self::url($base, [...$legacy['match'], 'op' => $op, $key => $record->id]),
                ];
            }

            $rows[] = [
                'id' => $record->id,
                'cells' => array_map(static fn (array $column): string => self::display($record->value($column['field'])), $list['columns']),
                'actions' => $actions,
            ];
        }

        $pages = [];
        if ($page->query->limit !== null && $page->total > $page->query->limit) {
            for ($start = 0, $number = 1; $start < $page->total; $start += $page->query->limit, $number++) {
                $pages[] = [
                    'number' => $number,
                    'current' => $start === $page->query->start,
                    'url' => self::url($base, [...$legacy['match'], "start{$key}" => $start, 'limitsel' => $page->query->limit]),
                ];
            }
        }

        return new HtmlString($this->views->make('ipf::admin.list', [
            'columns' => $columns,
            'rows' => $rows,
            'total' => $page->total,
            'pages' => $pages,
            'create' => $list['createButton'] ? ['label' => $createCaption, 'url' => self::url($base, [...$legacy['match'], 'op' => $legacy['form']])] : null,
            'search' => $list['quickSearch'] === [] ? null : [
                'name' => "quicksearch_{$descriptor::entityClass()}",
                'value' => $page->query->search,
                'action' => $base,
                'hidden' => $legacy['match'],
                'label' => $resource->text('_QSEARCH'),
                'submit' => $resource->text('_SEARCH'),
            ],
            'actionsLabel' => $resource->text('_CO_ICMS_ACTIONS'),
            'empty' => $resource->text('_CO_ICMS_NO_OBJECT'),
        ])->render());
    }

    /**
     * The confirmation of a deletion (Icms\Core\Message::confirm()), posting
     * the confirmed deletion with a security token.
     */
    public function confirmDeletion(IpfResource $resource, IpfRecord $record, string $previousPage, Request $request): Response
    {
        $descriptor = $resource->descriptor;
        $legacy = $this->legacy($descriptor);

        return $this->render('ipf::admin.confirm', [
            'question' => sprintf($resource->text('_CO_ICMS_DELETE_CONFIRM'), self::display($record->value($descriptor::identifier()))),
            'action' => $legacy['script'],
            'hidden' => [
                'op' => $legacy['delete'],
                $descriptor::primaryKey() => $record->id,
                $legacy['confirm'] => 1,
                'redirect_page' => $previousPage,
                ...$legacy['match'],
            ],
            'token' => new HtmlString($this->navigation->tokenField()),
            'submit' => $resource->text('_CO_ICMS_DELETE'),
            'cancel' => $resource->text('_CANCEL'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function field(IpfResource $resource, IpfRecord $record, string $name): array
    {
        $field = $resource->descriptor::field($name);
        $control = (string) $field['control'];
        if ($control === 'text' && $field['type'] === 'INT') {
            $control = 'integer';
        }

        return [
            'name' => $name,
            'control' => $field['readonly'] && ! $field['hidden'] ? 'readonly' : $control,
            'caption' => $resource->caption($name),
            'description' => $resource->legacy->description($name),
            'required' => (bool) $field['required'] && ! $field['hidden'],
            'multiple' => (bool) $field['multiple'],
            'options' => $field['options'] === null ? [] : $resource->legacy->options($name),
            'settings' => $field['settings'],
            'value' => $field['key'] ? ($record->id ?? 0) : $record->value($name),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function permissionField(IpfResource $resource, IpfRecord $record, string $name): array
    {
        return [
            'name' => $name,
            'control' => 'permission',
            'caption' => $resource->caption($name),
            'description' => $resource->legacy->description($name),
            'required' => false,
            'multiple' => true,
            'options' => $resource->legacy->groups(),
            'settings' => [],
            'value' => $record->groups($name),
        ];
    }

    /**
     * @param class-string<ResourceDescriptor> $descriptor
     * @return array<string, mixed>
     */
    private function legacy(string $descriptor): array
    {
        return $descriptor::legacy() ?? throw new LogicException($descriptor::key() . ' declares no legacy admin URL (#[LegacyOperations]).');
    }

    /**
     * @param array<string, scalar|null> $parameters
     */
    private static function url(string $path, array $parameters): string
    {
        $query = http_build_query(array_filter($parameters, static fn (mixed $value): bool => $value !== null && $value !== ''));

        return $query === '' ? $path : "{$path}?{$query}";
    }

    private static function display(mixed $value): string
    {
        return is_array($value) ? implode(', ', array_map('strval', $value)) : (string) $value;
    }
}
