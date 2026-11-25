<?php

declare(strict_types=1);

namespace App\Tests\Integration\Ipf;

use App\Ipf\Repository\LegacyHandlerRepository;
use App\Ipf\Resource\IpfResourceFactory;
use App\Ipf\Schema\MigrationGenerator;
use App\Ipf\Schema\SchemaContract;
use App\Ipf\Schema\SchemaDiff;
use App\Ipf\Schema\SchemaDifference;
use App\Legacy\LegacyRuntime;
use App\Tests\Support\LegacyAppTestCase;
use App\Tests\Support\TestSite;
use Illuminate\Support\Facades\Artisan;
use Modules\System\Generated\Mimetype\MimetypeSchema;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * The generated schema contract against the installed table, and the
 * additive migrations generated from it: the mimetype table agrees with its
 * definition; an expanded definition gets a migration that only adds, after
 * which the legacy handler still reads and writes the table.
 */
#[RunTestsInSeparateProcesses]
#[Group('spec:ipf-schema')]
final class SchemaDiffTest extends LegacyAppTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_REFERER'] = 'http://127.0.0.1/';
        $this->app->make(LegacyRuntime::class)->bootTo('online');
    }

    public function testTheMimetypeTableAgreesWithItsDefinition(): void
    {
        $differences = $this->diff()->compare(MimetypeSchema::class);

        self::assertSame([], array_filter($differences, static fn (SchemaDifference $difference): bool => $difference->kind !== SchemaDifference::COMPATIBLE));
        $notes = array_map(static fn (SchemaDifference $difference): string => "{$difference->subject}: {$difference->detail}", $differences);
        // the system module update has typed the columns as the updater does; the install SQL's KEY stays
        self::assertSame(['system_mimetype.mimetypeid: the key is indexed by mimetypeid, not a primary key'], $notes);

        self::assertSame(0, Artisan::call('icms:schema:diff', ['resource' => 'system.mimetype']));
    }

    public function testAnExpandedDefinitionGetsAnAdditiveMigration(): void
    {
        $differences = $this->diff()->compare(ExpandedMimetypeSchema::class);
        $additive = array_values(array_filter($differences, static fn (SchemaDifference $difference): bool => $difference->kind === SchemaDifference::ADDITIVE));
        self::assertSame(['system_mimetype.weight', 'system_mimetype.note'], array_map(static fn (SchemaDifference $difference): string => $difference->subject, $additive));

        $code = (new MigrationGenerator())->generate('system_mimetype', $differences);
        self::assertStringContainsString("\$table->integer('weight')->default(0);", $code);
        self::assertStringContainsString("\$table->string('note', 255)->default('');", $code);
        self::assertStringNotContainsString("dropColumn(['mimetypeid'", $code);
        self::assertSame(1, preg_match_all('/dropColumn/', $code), 'down() drops only the added columns');

        $file = TestSite::varDir() . '/expand-mimetype-' . getmypid() . '.php';
        file_put_contents($file, $code);
        $migration = require $file;
        unlink($file);

        $migration->up();
        try {
            self::assertSame([], array_filter($this->diff()->compare(ExpandedMimetypeSchema::class), static fn (SchemaDifference $difference): bool => $difference->kind !== SchemaDifference::COMPATIBLE));

            // the legacy handler still stores and reads mimetypes in the expanded table
            $repository = $this->app->make(IpfResourceFactory::class)->get('system.mimetype')->repository;
            self::assertInstanceOf(LegacyHandlerRepository::class, $repository);
            $saved = $repository->save(null, ['extension' => 'icmsx', 'types' => 'application/x-icms-x', 'name' => 'Expanded', 'dirname' => ['system']], []);
            self::assertTrue($saved->succeeded(), implode("\n", $saved->errors));
            self::assertSame('Expanded', $repository->find((int) $saved->id)?->value('name'));
        } finally {
            $migration->down();
        }

        self::assertSame([], array_filter($this->diff()->compare(MimetypeSchema::class), static fn (SchemaDifference $difference): bool => $difference->kind !== SchemaDifference::COMPATIBLE));
    }

    /**
     * A table as the install SQL creates it (varchar(60), VARCHAR for a
     * list) still works for legacy: compatible, with notes.
     */
    public function testNarrowerLegacyColumnsAreCompatible(): void
    {
        $connection = $this->app->make('db')->connection('icms');
        $connection->statement('ALTER TABLE ' . TestSite::table('system_mimetype') . ' MODIFY extension varchar(60) NOT NULL DEFAULT \'\', MODIFY dirname VARCHAR(255) NOT NULL');

        $differences = $this->diff()->compare(MimetypeSchema::class);

        self::assertSame([], array_filter($differences, static fn (SchemaDifference $difference): bool => $difference->kind !== SchemaDifference::COMPATIBLE));
        self::assertContains(
            'system_mimetype.dirname: varchar(255) where the updater would create text',
            array_map(static fn (SchemaDifference $difference): string => "{$difference->subject}: {$difference->detail}", $differences),
        );
    }

    public function testATypeOfAnotherFamilyIsIncompatible(): void
    {
        $differences = $this->diff()->compare(RetypedMimetypeSchema::class);

        self::assertContains(
            'system_mimetype.name: varchar(255) is not a int column',
            array_map(static fn (SchemaDifference $difference): string => "{$difference->subject}: {$difference->detail}", array_filter($differences, static fn (SchemaDifference $difference): bool => $difference->kind === SchemaDifference::INCOMPATIBLE)),
        );
    }

    private function diff(): SchemaDiff
    {
        return new SchemaDiff($this->app->make('db')->connection('icms'));
    }
}

final class ExpandedMimetypeSchema extends SchemaContract
{
    public const DATA = [
        'table' => 'system_mimetype',
        'columns' => MimetypeSchema::DATA['columns'] + [
            'weight' => ['family' => 'int', 'nullable' => false, 'default' => '0', 'autoIncrement' => false],
            'note' => ['family' => 'varchar', 'nullable' => false, 'default' => '', 'autoIncrement' => false],
        ],
        'primaryKey' => ['mimetypeid'],
        'relations' => [],
    ];
}

final class RetypedMimetypeSchema extends SchemaContract
{
    public const DATA = [
        'table' => 'system_mimetype',
        'columns' => ['name' => ['family' => 'int', 'nullable' => false, 'default' => '0', 'autoIncrement' => false]] + MimetypeSchema::DATA['columns'],
        'primaryKey' => ['mimetypeid'],
        'relations' => [],
    ];
}
