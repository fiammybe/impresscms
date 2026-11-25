<?php

declare(strict_types=1);

namespace App\Ipf\Resource;

use LogicException;

/**
 * Base of the generated <Item>Descriptor classes: the compiled definition of
 * a resource as data (DATA), with accessors. Language-dependent texts
 * (captions, descriptions) are not in it; App\Ipf\Resource\LegacyResource
 * reads them from the live entity.
 */
abstract class ResourceDescriptor
{
    /** @var array<string, mixed> */
    public const DATA = [];

    /**
     * @return array<string, mixed>
     */
    public static function data(): array
    {
        return static::DATA;
    }

    public static function key(): string
    {
        return (string) static::DATA['resource'];
    }

    public static function primaryKey(): string
    {
        return (string) static::DATA['primaryKey'];
    }

    public static function identifier(): string
    {
        return (string) static::DATA['identifier'];
    }

    public static function item(): string
    {
        return (string) static::DATA['item'];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function fields(): array
    {
        return static::DATA['fields'];
    }

    /**
     * @return array<string, mixed>
     */
    public static function field(string $name): array
    {
        return static::DATA['fields'][$name] ?? throw new LogicException(static::key() . " has no field \"{$name}\".");
    }

    /**
     * Fields and permissions of the legacy form, in its order.
     *
     * @return list<string>
     */
    public static function form(): array
    {
        return static::DATA['form'];
    }

    /**
     * @return list<string>
     */
    public static function permissions(): array
    {
        return static::DATA['permissions'];
    }

    public static function isPermission(string $name): bool
    {
        return in_array($name, static::DATA['permissions'], true);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function listView(): ?array
    {
        return static::DATA['list'];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function legacy(): ?array
    {
        return static::DATA['legacy'];
    }

    public static function supportsBlade(): bool
    {
        return (bool) static::DATA['support']['blade'];
    }

    public static function supportsValidation(): bool
    {
        return (bool) static::DATA['support']['validation'];
    }

    /**
     * Fields written from a validated form: shown or hidden on the form, not
     * the key, not read-only.
     *
     * @return list<string>
     */
    public static function writableFields(): array
    {
        $writable = [];
        foreach (static::DATA['fields'] as $name => $field) {
            if ($field['control'] !== null && ! $field['key'] && ! $field['readonly']) {
                $writable[] = $name;
            }
        }

        return $writable;
    }

    /**
     * The entity class name Icms\Ipf\View\Table uses in its parameter names
     * (quicksearch_SystemMimetype).
     */
    public static function entityClass(): string
    {
        return (string) static::DATA['entity'];
    }
}
