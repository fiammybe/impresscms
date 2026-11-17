<?php

declare(strict_types=1);

namespace App\Ipf\Metadata;

/**
 * The read-only description of an IPF object (design D2): what forms, lists
 * and the API need to know about it, whatever declares it (an IPF entity or
 * an ORM-mapped class).
 */
final class EntityDefinition
{
    public const SOURCE_IPF = 'ipf';
    public const SOURCE_ORM = 'orm';

    /**
     * @param array<string, FieldDefinition>                                  $fields      by name, in declaration order
     * @param list<array{name: string, caption: string, description: string}> $permissions the handler's permissions
     * @param string                                                          $className   the object's class
     */
    public function __construct(
        public readonly string $module,
        public readonly string $item,
        public readonly string $table,
        public readonly string $keyField,
        public readonly string $identifierField,
        public readonly ?string $summaryField,
        public readonly array $fields,
        public readonly array $permissions = [],
        public readonly string $source = self::SOURCE_IPF,
        public readonly string $className = '',
    ) {
    }

    public function name(): string
    {
        return "{$this->module}/{$this->item}";
    }

    public function hasField(string $name): bool
    {
        return isset($this->fields[$name]);
    }

    public function field(string $name): FieldDefinition
    {
        if (!isset($this->fields[$name])) {
            throw new \InvalidArgumentException(sprintf('The object "%s" has no field "%s".', $this->name(), $name));
        }

        return $this->fields[$name];
    }

    /**
     * Whether a list may sort, filter or search on this field.
     */
    public function isQueryable(string $name): bool
    {
        return isset($this->fields[$name]) && $this->fields[$name]->persistent;
    }

    /**
     * @return array<string, FieldDefinition> the fields stored in the table
     */
    public function persistentFields(): array
    {
        return array_filter($this->fields, static fn (FieldDefinition $field): bool => $field->persistent);
    }
}
