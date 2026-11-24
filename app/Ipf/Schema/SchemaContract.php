<?php

declare(strict_types=1);

namespace App\Ipf\Schema;

/**
 * Base of the generated <Item>Schema classes: the columns an IPF definition
 * needs (DATA), typed by family as Icms\Db\Legacy\Updater\Handler types them.
 */
abstract class SchemaContract
{
    /** @var array{table: string, columns: array<string, array{family: string, nullable: bool, default: ?string, autoIncrement: bool}>, primaryKey: list<string>, relations: list<array<string, mixed>>} */
    public const DATA = ['table' => '', 'columns' => [], 'primaryKey' => [], 'relations' => []];

    public static function table(): string
    {
        return static::DATA['table'];
    }

    /**
     * @return array<string, array{family: string, nullable: bool, default: ?string, autoIncrement: bool}>
     */
    public static function columns(): array
    {
        return static::DATA['columns'];
    }

    /**
     * @return list<string>
     */
    public static function primaryKey(): array
    {
        return static::DATA['primaryKey'];
    }
}
