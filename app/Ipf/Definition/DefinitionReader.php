<?php

declare(strict_types=1);

namespace App\Ipf\Definition;

use Icms\Ipf\Metadata\LegacyOperations;
use Icms\Ipf\Metadata\ListColumn;
use Icms\Ipf\Metadata\ListFilter;
use Icms\Ipf\Metadata\ListView;
use Icms\Ipf\Metadata\Notification;
use Icms\Ipf\Metadata\Relation;
use ReflectionAttribute;
use ReflectionClass;

/**
 * Reads the canonical definition of an IPF resource from its live legacy
 * declaration: a handler, an entity the handler created, and the metadata
 * attributes on the handler class (Icms\Ipf\Metadata).
 *
 * The entity's $vars and $controls are copied, never changed: the reader does
 * not call getForm() or Table::render(), which set controls and read the
 * request. The control a variable without its own control gets is the one
 * Icms\Ipf\Form\Base::createElements() would give it (DataType::defaultControl()).
 *
 * Anything the model cannot represent raises UnsupportedDefinitionException
 * instead of being dropped: unknown variable or control keys, unknown control
 * names, and attributes naming unknown variables. A control's itemHandler is
 * an option provider, never a relation.
 */
final class DefinitionReader
{
    /** keys Icms\Core\Entity::initVar() and Icms\Ipf\Entity add to a variable */
    private const VAR_KEYS = [
        'value', 'required', 'data_type', 'maxlength', 'changed', 'options', 'multilingual', 'form_caption', 'form_dsc',
        'sortby', 'persistent', 'displayOnForm', 'displayOnSingleView', 'readonly', 'not_gpc', 'advancedform', 'itemName', 'size',
    ];

    /** keys the legacy form elements read from a control */
    private const CONTROL_KEYS = [
        'name', 'itemHandler', 'module', 'method', 'params', 'object', 'options', 'value', 'size', 'maxlength', 'rows', 'cols',
        'form_editor', 'form_rows', 'syntax', 'language', 'width', 'height', 'close', 'all', 'nourl', 'delimeter', 'onSelect',
    ];

    /** control keys that describe the element, not its data */
    private const PRESENTATION_KEYS = ['size', 'maxlength', 'rows', 'cols', 'form_editor', 'form_rows', 'syntax', 'language', 'width', 'height', 'close', 'all', 'nourl', 'delimeter', 'onSelect', 'value'];

    /** controls Icms\Ipf\Form\Base::getControl() knows (its switch and Icms\Ipf\Form\Elements) */
    private const CONTROLS = [
        'autocomplete', 'blockoptions', 'checkbox', 'color', 'country', 'date', 'datetime', 'dhtmltextarea', 'file',
        'fileupload', 'group', 'group_multi', 'image', 'imageupload', 'label', 'language', 'page', 'parentcategory',
        'password', 'passwordtray', 'radio', 'richfile', 'section', 'select', 'selectmulti', 'signature', 'source',
        'sourceeditor', 'text', 'textarea', 'theme', 'theme_multi', 'time', 'timezone', 'upload', 'urllink', 'user',
        'user_multi', 'yesno',
    ];

    /** controls whose choices come from the control (Icms\Ipf\Form\Elements\Select and its subclasses) */
    private const CHOICE_CONTROLS = ['select', 'selectmulti', 'radio', 'checkbox'];

    /**
     * @param string $tablePrefix the installation's table prefix with its
     *     separator (`icms_`), removed from the handler's table name
     */
    public function __construct(
        private readonly string $legacyRoot,
        private readonly string $tablePrefix = '',
    ) {
    }

    public function read(object $handler, ?object $entity = null): ResourceDefinition
    {
        $entity ??= $handler->create();
        $handlerClass = new ReflectionClass($handler);
        $provenance = new Provenance(
            $entity::class,
            $handlerClass->isAnonymous() ? (string) $handlerClass->getParentClass()?->getName() : $handler::class,
            $this->relative((string) $handlerClass->getFileName()),
            (int) $handlerClass->getStartLine(),
        );

        $keyName = $handler->keyName;
        if (! is_string($keyName)) {
            throw UnsupportedDefinitionException::at($provenance->describe(), 'A composite key (' . implode(', ', (array) $keyName) . ') is not supported');
        }

        $fields = [];
        foreach ($entity->vars as $name => $var) {
            $fields[(string) $name] = $this->field($provenance, (string) $name, (array) $var, $entity->controls[$name] ?? null, $keyName);
        }

        $definition = new ResourceDefinition(
            module: (string) $handler->_moduleName,
            item: (string) $handler->_itemname,
            table: $this->table($provenance, (string) $handler->table),
            primaryKey: $keyName,
            identifier: (string) $handler->identifierName,
            summary: (string) $handler->summaryName,
            fields: $fields,
            permissions: $this->permissions((array) $handler->getPermissions()),
            list: $this->listDefinition($handlerClass, (string) $handler->identifierName),
            relations: $this->relations($handlerClass),
            legacy: $this->legacyOperations($handlerClass),
            notifications: $this->notifications($handlerClass),
            provenance: $provenance,
        );

        $this->assertFieldsExist($definition);

        return $definition;
    }

    /**
     * @param array<string, mixed> $var
     */
    private function field(Provenance $provenance, string $name, array $var, mixed $control, string $keyName): FieldDefinition
    {
        $where = $provenance->field($name);
        $unknown = array_diff(array_keys($var), self::VAR_KEYS);
        if ($unknown !== []) {
            throw UnsupportedDefinitionException::at($where, 'Unknown variable keys ' . implode(', ', $unknown));
        }

        $type = DataType::tryFrom((int) $var['data_type'])
            ?? throw UnsupportedDefinitionException::at($where, "Unknown data type {$var['data_type']}");

        $isKey = $name === $keyName;
        $displayOnForm = (bool) ($var['displayOnForm'] ?? true);
        $maxLength = (int) ($var['maxlength'] ?? 0);

        return new FieldDefinition(
            name: $name,
            type: $type,
            default: $var['value'] ?? null,
            required: (bool) ($var['required'] ?? false),
            maxLength: $maxLength > 0 ? $maxLength : null,
            persistent: (bool) ($var['persistent'] ?? true),
            displayOnForm: $displayOnForm,
            displayOnSingleView: (bool) ($var['displayOnSingleView'] ?? true),
            readonly: (bool) ($var['readonly'] ?? false),
            sortable: (bool) ($var['sortby'] ?? false),
            multilingual: (bool) ($var['multilingual'] ?? false),
            valueOptions: is_string($var['options'] ?? null) && $var['options'] !== '' ? $var['options'] : null,
            control: $isKey || ! $displayOnForm
                ? new ControlDefinition('hidden', false)
                : $this->control($where, $type, $control),
            caption: (string) ($var['form_caption'] ?? ''),
            description: (string) ($var['form_dsc'] ?? ''),
            isKey: $isKey,
        );
    }

    private function control(string $where, DataType $type, mixed $control): ?ControlDefinition
    {
        if ($control === null || $control === false) {
            $default = $type->defaultControl();

            return $default === null ? null : new ControlDefinition($default, false);
        }

        $control = (array) $control;
        $unknown = array_diff(array_keys($control), self::CONTROL_KEYS);
        if ($unknown !== []) {
            throw UnsupportedDefinitionException::at($where, 'Unknown control keys ' . implode(', ', $unknown));
        }

        // as Icms\Ipf\Form\Base: a control without a name is a select
        $name = (string) (($control['name'] ?? '') ?: 'select');
        if (! in_array($name, self::CONTROLS, true)) {
            throw UnsupportedDefinitionException::at($where, "Unknown control \"{$name}\"");
        }

        $settings = [];
        foreach (self::PRESENTATION_KEYS as $key) {
            if (array_key_exists($key, $control) && (is_scalar($control[$key]) || $control[$key] === null)) {
                $settings[$key] = $control[$key];
            }
        }

        ksort($settings);

        return new ControlDefinition($name, true, $this->optionProvider($where, $name, $control), $settings);
    }

    /**
     * @param array<string, mixed> $control
     */
    private function optionProvider(string $where, string $name, array $control): ?OptionProvider
    {
        if (isset($control['options'])) {
            if (! is_array($control['options'])) {
                throw UnsupportedDefinitionException::at($where, 'Control options that are not an array');
            }

            return new OptionProvider(OptionProvider::LITERAL, options: $control['options']);
        }

        if (isset($control['object'])) {
            return new OptionProvider(OptionProvider::OBJECT, handler: get_class($control['object']), method: (string) ($control['method'] ?? ''));
        }

        if (isset($control['itemHandler'])) {
            return new OptionProvider(
                OptionProvider::ITEM_HANDLER,
                handler: (string) $control['itemHandler'],
                module: isset($control['module']) ? (string) $control['module'] : null,
                method: (string) ($control['method'] ?? ''),
                params: array_values((array) ($control['params'] ?? [])),
            );
        }

        if (isset($control['method'])) {
            return new OptionProvider(OptionProvider::HANDLER, method: (string) $control['method'], params: array_values((array) ($control['params'] ?? [])));
        }

        if (in_array($name, self::CHOICE_CONTROLS, true)) {
            throw UnsupportedDefinitionException::at($where, "A \"{$name}\" control without options, object, itemHandler or method");
        }

        return null;
    }

    /**
     * @param list<array<string, mixed>> $permissions
     * @return list<PermissionDefinition>
     */
    private function permissions(array $permissions): array
    {
        return array_values(array_map(static fn (array $permission): PermissionDefinition => new PermissionDefinition(
            (string) $permission['perm_name'],
            (string) ($permission['caption'] ?? ''),
            (string) ($permission['description'] ?? ''),
        ), $permissions));
    }

    private function listDefinition(ReflectionClass $class, string $identifier): ?ListDefinition
    {
        $view = $this->attribute($class, ListView::class);
        $columns = array_map(static fn (ListColumn $column): array => [
            'field' => $column->field,
            'align' => $column->align,
            'width' => $column->width,
            'sortable' => $column->sortable,
        ], $this->attributes($class, ListColumn::class));
        $filters = array_map(static fn (ListFilter $filter): array => [
            'field' => $filter->field,
            'method' => $filter->method,
            'default' => $filter->default,
        ], $this->attributes($class, ListFilter::class));

        if ($view === null) {
            if ($columns !== [] || $filters !== []) {
                throw UnsupportedDefinitionException::at($class->getName(), 'List columns or filters without #[ListView]');
            }

            return null;
        }

        $order = strtoupper($view->order);
        if (! in_array($order, ['ASC', 'DESC'], true)) {
            throw UnsupportedDefinitionException::at($class->getName(), "List order \"{$view->order}\"");
        }

        return new ListDefinition(
            columns: array_values($columns),
            quickSearch: array_values($view->quickSearch),
            filters: array_values($filters),
            actions: array_values($view->actions),
            createButton: $view->createButton,
            sortBy: $view->sortBy ?? $identifier,
            order: $order,
            limit: $view->limit,
        );
    }

    /**
     * @return list<RelationDefinition>
     */
    private function relations(ReflectionClass $class): array
    {
        $relations = [];
        foreach ($this->attributes($class, Relation::class) as $relation) {
            if (! in_array($relation->cardinality, [Relation::BELONGS_TO, Relation::HAS_MANY, Relation::BELONGS_TO_MANY], true)) {
                throw UnsupportedDefinitionException::at($class->getName(), "Relation cardinality \"{$relation->cardinality}\"");
            }

            if (! in_array($relation->ownership, ['owner', 'reference'], true)) {
                throw UnsupportedDefinitionException::at($class->getName(), "Relation ownership \"{$relation->ownership}\"");
            }

            if (($relation->cardinality === Relation::BELONGS_TO_MANY) !== ($relation->joinTable !== null)) {
                throw UnsupportedDefinitionException::at($class->getName(), 'A join table is required for, and only for, belongsToMany relations');
            }

            $relations[] = new RelationDefinition($relation->field, $relation->target, $relation->cardinality, $relation->ownership, $relation->foreignKey, $relation->joinTable);
        }

        return $relations;
    }

    private function legacyOperations(ReflectionClass $class): ?LegacyOperationsDefinition
    {
        $legacy = $this->attribute($class, LegacyOperations::class);
        if ($legacy === null) {
            return null;
        }

        $ops = [$legacy->form, $legacy->save, $legacy->delete];
        if (in_array('', $ops, true) || count(array_unique($ops)) !== 3) {
            throw UnsupportedDefinitionException::at($class->getName(), 'Legacy operations must be distinct and not empty');
        }

        $match = $legacy->match;
        ksort($match);

        return new LegacyOperationsDefinition('/' . ltrim($legacy->script, '/'), array_map('strval', $match), $legacy->form, $legacy->save, $legacy->delete, $legacy->confirm);
    }

    /**
     * @return list<NotificationDefinition>
     */
    private function notifications(ReflectionClass $class): array
    {
        return array_map(static function (Notification $notification) use ($class): NotificationDefinition {
            if (! in_array($notification->after, ['afterInsert', 'afterUpdate', 'afterSave', 'afterDelete'], true)) {
                throw UnsupportedDefinitionException::at($class->getName(), "Notification after \"{$notification->after}\"");
            }

            return new NotificationDefinition($notification->category, $notification->event, $notification->after);
        }, $this->attributes($class, Notification::class));
    }

    private function assertFieldsExist(ResourceDefinition $definition): void
    {
        $named = [];
        if ($definition->list !== null) {
            $named = [
                ...array_column($definition->list->columns, 'field'),
                ...$definition->list->quickSearch,
                ...array_column($definition->list->filters, 'field'),
                $definition->list->sortBy,
            ];
        }

        foreach ($definition->relations as $relation) {
            $named[] = $relation->field;
        }

        foreach (array_unique($named) as $field) {
            if (! isset($definition->fields[$field])) {
                throw UnsupportedDefinitionException::at($definition->provenance->describe(), "Metadata names the unknown variable \"{$field}\"");
            }
        }
    }

    /**
     * @template T of object
     * @param class-string<T> $attribute
     * @return T|null
     */
    private function attribute(ReflectionClass $class, string $attribute): ?object
    {
        return $this->attributes($class, $attribute)[0] ?? null;
    }

    /**
     * Attributes of the handler class and of its parents (an anonymous or
     * derived handler keeps the metadata of the class it extends).
     *
     * @template T of object
     * @param class-string<T> $attribute
     * @return list<T>
     */
    private function attributes(ReflectionClass $class, string $attribute): array
    {
        for ($current = $class; $current !== false; $current = $current->getParentClass()) {
            $found = $current->getAttributes($attribute, ReflectionAttribute::IS_INSTANCEOF);
            if ($found !== []) {
                return array_map(static fn (ReflectionAttribute $item): object => $item->newInstance(), $found);
            }
        }

        return [];
    }

    /**
     * The table without the installation's prefix, so definitions do not
     * depend on the installation; the `icms` connection adds it back.
     */
    private function table(Provenance $provenance, string $table): string
    {
        if ($this->tablePrefix === '') {
            return $table;
        }

        if (! str_starts_with($table, $this->tablePrefix)) {
            throw UnsupportedDefinitionException::at($provenance->describe(), "The table \"{$table}\" lacks the prefix \"{$this->tablePrefix}\"");
        }

        return substr($table, strlen($this->tablePrefix));
    }

    private function relative(string $file): string
    {
        $file = str_replace('\\', '/', $file);
        $root = rtrim(str_replace('\\', '/', $this->legacyRoot), '/') . '/';

        return str_starts_with($file, $root) ? substr($file, strlen($root)) : basename($file);
    }
}
