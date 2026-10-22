<?php

declare(strict_types=1);

namespace App\Tests\Integration\Capability;

use App\Tests\Support\CapabilitySite;
use App\Tests\Support\DatabaseTestCase;
use App\Tests\Support\TestSite;
use PDO;

/**
 * The two configuration tables of the framework (design D7): created empty by
 * an install, and again by the system module update of an existing site.
 */
final class ConfigurationTablesTest extends DatabaseTestCase
{
    private const TABLES = ['system_capabilityassignment', 'system_capabilityprovider'];

    public function testAFreshInstallCreatesTheEmptyTables(): void
    {
        $pdo = TestSite::serverConnection();
        $schema = TestSite::snapshotSchema();

        foreach (self::TABLES as $table) {
            $count = $pdo->query('SELECT COUNT(*) FROM ' . TestSite::quoteIdentifier($schema) . '.' . TestSite::quoteIdentifier(TestSite::table($table)))->fetchColumn();
            self::assertSame(0, (int) $count, "{$table} exists and is empty after bin/test-db");
        }
    }

    public function testTheSystemModuleUpdateCreatesThemOnAnExistingSite(): void
    {
        $pdo = TestSite::connection();

        foreach (self::TABLES as $table) {
            $pdo->exec('DROP TABLE ' . TestSite::quoteIdentifier(TestSite::table($table)));
        }

        CapabilitySite::admin('update', 'system');

        foreach (self::TABLES as $table) {
            $tables = $pdo->query("SHOW TABLES LIKE '" . TestSite::table($table) . "'")->fetchAll(PDO::FETCH_COLUMN);
            self::assertSame([TestSite::table($table)], $tables);
        }
    }
}
