<?php

declare(strict_types=1);

namespace App\Ipf\Schema;

use Illuminate\Database\Connection;

/**
 * Compares a generated schema contract (SchemaContract) with the live table
 * on the shared `icms` connection.
 *
 * Types are compared by family, as Icms\Db\Legacy\Updater\Handler types a
 * variable, not by exact DDL: the install SQL of many tables differs from
 * what the updater would create (narrower varchars, KEY instead of PRIMARY
 * KEY), and legacy works with them. Nothing here drops, renames or narrows;
 * missing columns and join tables are additive, everything else is reported.
 */
final class SchemaDiff
{
    /** live MySQL data types accepted for each contract family; the second list is accepted with a note */
    private const FAMILIES = [
        'varchar' => [['varchar', 'char'], ['tinytext', 'text', 'mediumtext', 'longtext']],
        'text' => [['tinytext', 'text', 'mediumtext', 'longtext'], ['varchar', 'char']],
        'int' => [['int', 'integer', 'tinyint', 'smallint', 'mediumint', 'bigint'], []],
        'float' => [['float', 'double', 'decimal'], []],
    ];

    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @param class-string<SchemaContract> $contract
     * @return list<SchemaDifference>
     */
    public function compare(string $contract): array
    {
        $table = $contract::table();
        $live = $this->columns($table);
        if ($live === []) {
            return [new SchemaDifference(SchemaDifference::INCOMPATIBLE, $table, 'the table does not exist; legacy installs it (install SQL or module installation)')];
        }

        $differences = [];
        foreach ($contract::columns() as $name => $column) {
            if (! isset($live[$name])) {
                $differences[] = new SchemaDifference(SchemaDifference::ADDITIVE, "{$table}.{$name}", "missing {$column['family']} column", ['name' => $name] + $column);

                continue;
            }

            $differences = [...$differences, ...$this->compareColumn($table, $name, $column, $live[$name])];
        }

        foreach (array_diff(array_keys($live), array_keys($contract::columns())) as $extra) {
            $differences[] = new SchemaDifference(SchemaDifference::COMPATIBLE, "{$table}.{$extra}", 'column not in the definition; kept for legacy');
        }

        $indexes = $this->indexes($table);
        foreach ($contract::primaryKey() as $key) {
            $index = $indexes[$key] ?? null;
            if ($index === null) {
                $differences[] = new SchemaDifference(SchemaDifference::INCOMPATIBLE, "{$table}.{$key}", 'the key is not indexed');
            } elseif ($index !== 'PRIMARY') {
                $differences[] = new SchemaDifference(SchemaDifference::COMPATIBLE, "{$table}.{$key}", "the key is indexed by {$index}, not a primary key");
            }
        }

        foreach ($contract::DATA['relations'] as $relation) {
            if ($relation['joinTable'] !== null && $this->columns((string) $relation['joinTable']) === []) {
                $differences[] = new SchemaDifference(SchemaDifference::ADDITIVE, (string) $relation['joinTable'], "missing join table of {$relation['field']}", ['joinTable' => $relation['joinTable']]);
            }
        }

        return $differences;
    }

    /**
     * @param array{family: string, nullable: bool, default: ?string, autoIncrement: bool} $column
     * @param array{type: string, columnType: string, nullable: bool, default: ?string, extra: string} $live
     * @return list<SchemaDifference>
     */
    private function compareColumn(string $table, string $name, array $column, array $live): array
    {
        $subject = "{$table}.{$name}";
        [$exact, $noted] = self::FAMILIES[$column['family']];
        if (in_array($live['type'], $noted, true)) {
            return [new SchemaDifference(SchemaDifference::COMPATIBLE, $subject, "{$live['columnType']} where the updater would create {$column['family']}")];
        }

        if (! in_array($live['type'], $exact, true)) {
            return [new SchemaDifference(SchemaDifference::INCOMPATIBLE, $subject, "{$live['columnType']} is not a {$column['family']} column")];
        }

        $differences = [];
        if ($column['autoIncrement'] && ! str_contains($live['extra'], 'auto_increment')) {
            $differences[] = new SchemaDifference(SchemaDifference::INCOMPATIBLE, $subject, 'the key is not auto_increment');
        }

        if ($live['nullable'] && ! $column['nullable']) {
            $differences[] = new SchemaDifference(SchemaDifference::COMPATIBLE, $subject, 'nullable where the updater creates NOT NULL');
        }

        return $differences;
    }

    /**
     * @return array<string, array{type: string, columnType: string, nullable: bool, default: ?string, extra: string}>
     */
    private function columns(string $table): array
    {
        $rows = $this->connection->select(
            'SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, EXTRA FROM information_schema.COLUMNS'
            . ' WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? ORDER BY ORDINAL_POSITION',
            [$this->connection->getTablePrefix() . $table],
        );

        $columns = [];
        foreach ($rows as $row) {
            $row = array_change_key_case((array) $row, CASE_UPPER);
            $columns[(string) $row['COLUMN_NAME']] = [
                'type' => strtolower((string) $row['DATA_TYPE']),
                'columnType' => strtolower((string) $row['COLUMN_TYPE']),
                'nullable' => $row['IS_NULLABLE'] === 'YES',
                'default' => $row['COLUMN_DEFAULT'] === null ? null : (string) $row['COLUMN_DEFAULT'],
                'extra' => strtolower((string) $row['EXTRA']),
            ];
        }

        return $columns;
    }

    /**
     * The first index of each column that starts one.
     *
     * @return array<string, string> column => index name
     */
    private function indexes(string $table): array
    {
        $rows = $this->connection->select(
            'SELECT INDEX_NAME, COLUMN_NAME FROM information_schema.STATISTICS'
            . ' WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND SEQ_IN_INDEX = 1 ORDER BY INDEX_NAME = \'PRIMARY\' DESC, INDEX_NAME',
            [$this->connection->getTablePrefix() . $table],
        );

        $indexes = [];
        foreach ($rows as $row) {
            $row = array_change_key_case((array) $row, CASE_UPPER);
            $indexes[(string) $row['COLUMN_NAME']] ??= (string) $row['INDEX_NAME'];
        }

        return $indexes;
    }
}
