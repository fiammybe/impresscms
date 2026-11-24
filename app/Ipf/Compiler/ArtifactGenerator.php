<?php

declare(strict_types=1);

namespace App\Ipf\Compiler;

use App\Ipf\Definition\DataType;
use App\Ipf\Definition\FieldDefinition;
use App\Ipf\Definition\ResourceDefinition;
use App\Ipf\Resource\ResourceRegistration;

/**
 * The Laravel artifacts of one IPF resource, generated from its canonical
 * definition. Every file is wholly generated: hand-written code lives beside
 * the Generated directory and uses these classes, it never edits them.
 *
 * - <Item>Descriptor: fields, form order, list, legacy URL, permissions,
 *   relations and what the Laravel side supports, as data
 * - <Item>Schema: the column contract for icms:schema:diff
 * - <Item>Request: the FormRequest validating the legacy form payload
 * - <Item>Policy: abilities mapped to ImpressCMS access and permissions
 * - <Item>: the Eloquent model for reads, mapped to the legacy table
 *
 * Output is a pure function of the definition: no captions (language
 * strings), no table prefix, no absolute paths, no timestamps.
 */
final class ArtifactGenerator
{
    public const GENERATOR = 'php artisan icms:definitions:compile';

    /** controls App\Ipf\View renders with Blade components */
    public const BLADE_CONTROLS = ['hidden', 'text', 'textarea', 'select', 'selectmulti', 'label'];

    public function __construct(private readonly RuleCompiler $rules = new RuleCompiler())
    {
    }

    /**
     * @return array<string, string> file name => contents
     */
    public function generate(ResourceDefinition $definition, ResourceRegistration $registration): array
    {
        $class = $registration->className();
        $compiled = $this->rules->compile($definition);

        $files = [
            "{$class}Descriptor.php" => $this->descriptor($definition, $registration, $compiled['unsupported']),
            "{$class}Schema.php" => $this->schema($definition, $registration),
            "{$class}Policy.php" => $this->policy($definition, $registration),
            "{$class}.php" => $this->model($definition, $registration),
        ];

        if ($compiled['unsupported'] === []) {
            $files["{$class}Request.php"] = $this->request($definition, $registration, $compiled['rules'], $compiled['messages']);
        }

        ksort($files);

        return $files;
    }

    /**
     * @param array<string, string> $unsupportedValidation
     */
    private function descriptor(ResourceDefinition $definition, ResourceRegistration $registration, array $unsupportedValidation): string
    {
        $fields = [];
        $unsupportedBlade = [];
        foreach ($definition->fields as $name => $field) {
            $fields[$name] = self::fieldData($field);
            if ($field->onForm() && ! $field->readonly && ! in_array($field->control?->name, self::BLADE_CONTROLS, true)) {
                $unsupportedBlade[$name] = (string) $field->control?->name;
            }
        }

        $list = $definition->list;
        $data = [
            'resource' => $definition->key(),
            'module' => $definition->module,
            'item' => $definition->item,
            'entity' => $definition->provenance->entityClass,
            'handler' => $registration->handler,
            'table' => $definition->table,
            'primaryKey' => $definition->primaryKey,
            'identifier' => $definition->identifier,
            'summary' => $definition->summary,
            'fields' => $fields,
            'form' => array_values(array_merge(
                array_keys(array_filter($definition->fields, static fn (FieldDefinition $field): bool => $field->onForm())),
                array_map(static fn ($permission): string => $permission->name, $definition->permissions),
            )),
            'permissions' => array_map(static fn ($permission): string => $permission->name, $definition->permissions),
            'list' => $list === null ? null : [
                'columns' => $list->columns,
                'quickSearch' => $list->quickSearch,
                'filters' => $list->filters,
                'actions' => $list->actions,
                'createButton' => $list->createButton,
                'sortBy' => $list->sortBy,
                'order' => $list->order,
                'limit' => $list->limit,
            ],
            'legacy' => $definition->legacy === null ? null : [
                'script' => $definition->legacy->script,
                'match' => $definition->legacy->match,
                'form' => $definition->legacy->form,
                'save' => $definition->legacy->save,
                'delete' => $definition->legacy->delete,
                'confirm' => $definition->legacy->confirm,
            ],
            'relations' => array_map(static fn ($relation): array => [
                'field' => $relation->field,
                'target' => $relation->target,
                'cardinality' => $relation->cardinality,
                'ownership' => $relation->ownership,
                'foreignKey' => $relation->foreignKey,
                'joinTable' => $relation->joinTable,
            ], $definition->relations),
            'notifications' => array_map(static fn ($notification): array => [
                'category' => $notification->category,
                'event' => $notification->event,
                'after' => $notification->after,
            ], $definition->notifications),
            'support' => [
                'validation' => $unsupportedValidation === [],
                'blade' => $unsupportedBlade === [],
                'unsupported' => ['validation' => $unsupportedValidation, 'blade' => $unsupportedBlade],
            ],
        ];

        $body = <<<PHP
            final class {$registration->className()}Descriptor extends \\App\\Ipf\\Resource\\ResourceDescriptor
            {
                public const DATA = {$this->export($data, 1)};
            }
            PHP;

        return $this->file($definition, $registration, 'The definition of the resource as data: fields, form, list, legacy URL and support.', $body);
    }

    private function schema(ResourceDefinition $definition, ResourceRegistration $registration): string
    {
        $columns = [];
        foreach ($definition->fields as $name => $field) {
            $family = $field->type->columnFamily();
            if (! $field->persistent || $family === null) {
                continue;
            }

            $columns[$name] = [
                'family' => $family,
                'nullable' => false,
                'default' => $field->isKey ? null : self::columnDefault($field),
                'autoIncrement' => $field->isKey && $field->type === DataType::INT,
            ];
        }

        $data = [
            'table' => $definition->table,
            'columns' => $columns,
            'primaryKey' => [$definition->primaryKey],
            'relations' => array_map(static fn ($relation): array => [
                'field' => $relation->field,
                'target' => $relation->target,
                'joinTable' => $relation->joinTable,
            ], $definition->relations),
        ];

        $body = <<<PHP
            final class {$registration->className()}Schema extends \\App\\Ipf\\Schema\\SchemaContract
            {
                public const DATA = {$this->export($data, 1)};
            }
            PHP;

        return $this->file($definition, $registration, 'The columns the IPF definition needs, as Icms\Db\Legacy\Updater types them.', $body);
    }

    /**
     * @param array<string, list<string>> $rules
     * @param array<string, list<string|int>> $messages
     */
    private function request(ResourceDefinition $definition, ResourceRegistration $registration, array $rules, array $messages): string
    {
        $resource = var_export($definition->key(), true);
        $body = <<<PHP
            final class {$registration->className()}Request extends \\App\\Ipf\\Http\\IpfFormRequest
            {
                public const RESOURCE = {$resource};

                /**
                 * @return array<string, list<string>>
                 */
                public function rules(): array
                {
                    return {$this->export($rules, 2)};
                }

                /**
                 * @return array<string, list<string|int>> message key => [language constant or format, field, …arguments]
                 */
                protected function messageTemplates(): array
                {
                    return {$this->export($messages, 2)};
                }
            }
            PHP;

        return $this->file($definition, $registration, 'Validates the posted legacy form; it neither binds nor stores.', $body);
    }

    private function policy(ResourceDefinition $definition, ResourceRegistration $registration): string
    {
        $access = $definition->module === 'system' && isset($definition->legacy?->match['fct'])
            ? ['system_function' => $definition->legacy->match['fct']]
            : ['module_admin' => $definition->module];

        $resource = var_export($definition->key(), true);
        $permissions = $this->export(array_map(static fn ($permission): string => $permission->name, $definition->permissions), 1);
        $accessCode = $this->export($access, 1);
        $body = <<<PHP
            final class {$registration->className()}Policy extends \\App\\Ipf\\Auth\\IpfResourcePolicy
            {
                public const RESOURCE = {$resource};

                /** who administers the resource */
                protected const ACCESS = {$accessCode};

                /** the handler's per-item permissions (Icms\\Ipf\\Handler::addPermission()) */
                protected const PERMISSIONS = {$permissions};
            }
            PHP;

        return $this->file($definition, $registration, 'Maps the resource abilities to ImpressCMS administration rights and item permissions.', $body);
    }

    private function model(ResourceDefinition $definition, ResourceRegistration $registration): string
    {
        $casts = [];
        foreach ($definition->fields as $name => $field) {
            if (! $field->persistent || $field->type->columnFamily() === null) {
                continue;
            }

            $cast = match ($field->type) {
                DataType::INT, DataType::STIME, DataType::MTIME, DataType::LTIME, DataType::TIME_ONLY, DataType::URLLINK, DataType::FILE => 'integer',
                DataType::FLOAT => 'float',
                DataType::SIMPLE_ARRAY => \App\Ipf\Eloquent\Casts\PipeList::class,
                DataType::ARRAY => \App\Ipf\Eloquent\Casts\LegacySerialized::class,
                default => null,
            };
            if ($cast !== null) {
                $casts[$name] = $cast;
            }
        }

        $resource = var_export($definition->key(), true);
        $table = var_export($definition->table, true);
        $key = var_export($definition->primaryKey, true);
        $castsCode = $this->export($casts, 1);
        $body = <<<PHP
            final class {$registration->className()} extends \\App\\Ipf\\Eloquent\\IpfModel
            {
                public const RESOURCE = {$resource};

                /** without the table prefix: the `icms` connection adds it */
                protected \$table = {$table};

                protected \$primaryKey = {$key};

                /** @var array<string, string> */
                protected \$casts = {$castsCode};
            }
            PHP;

        return $this->file($definition, $registration, 'Reads the legacy table; writes go through the IPF handler.', $body);
    }

    /**
     * @return array<string, mixed>
     */
    private static function fieldData(FieldDefinition $field): array
    {
        return [
            'type' => $field->type->name,
            'control' => $field->control?->name,
            'multiple' => (bool) $field->control?->isMultiple(),
            'options' => $field->control?->options?->toArray(),
            'settings' => $field->control === null ? [] : $field->control->settings,
            'required' => $field->required,
            'maxLength' => $field->maxLength,
            'default' => is_scalar($field->default) || $field->default === null || is_array($field->default) ? $field->default : null,
            'persistent' => $field->persistent,
            'readonly' => $field->readonly,
            'hidden' => $field->isHidden(),
            'key' => $field->isKey,
            'sortable' => $field->sortable,
            'multilingual' => $field->multilingual,
            'valueOptions' => $field->valueOptions,
        ];
    }

    /**
     * Icms\Db\Legacy\Updater\Handler::getFieldDefaultFromVar(): no default for
     * text types, otherwise the variable's value, 0 or ''.
     */
    private static function columnDefault(FieldDefinition $field): ?string
    {
        if (in_array($field->type, [DataType::TXTAREA, DataType::SOURCE, DataType::OTHER, DataType::SIMPLE_ARRAY, DataType::CURRENCY, DataType::ARRAY], true)) {
            return null;
        }

        if ($field->default !== null && $field->default !== '' && $field->default !== false && $field->default !== 0 && $field->default !== '0' && is_scalar($field->default)) {
            return (string) $field->default;
        }

        return $field->type->columnFamily() === 'int' || $field->type === DataType::FLOAT ? '0' : '';
    }

    private function export(mixed $value, int $indent): string
    {
        return PhpExporter::export($value, $indent);
    }

    private function file(ResourceDefinition $definition, ResourceRegistration $registration, string $summary, string $body): string
    {
        $source = $definition->provenance->describe();

        return <<<PHP
            <?php

            /*
             * GENERATED FILE - DO NOT EDIT.
             *
             * Generator: {$this->generatorLine()}
             * Resource:  {$definition->key()}
             * Source:    {$source}
             *
             * {$summary}
             * Change the IPF definition and run `php artisan icms:definitions:compile`;
             * `php artisan icms:definitions:check` fails when this file differs from it.
             */

            declare(strict_types=1);

            namespace {$registration->namespace()};

            {$body}

            PHP;
    }

    private function generatorLine(): string
    {
        return self::GENERATOR;
    }
}
