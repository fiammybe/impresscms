<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Ipf\Resource\ResourceRegistry;
use App\Ipf\Schema\MigrationGenerator;
use App\Ipf\Schema\SchemaContract;
use App\Ipf\Schema\SchemaDiff;
use App\Ipf\Schema\SchemaDifference;
use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;

/**
 * Compares the schema contracts of the IPF resources with their live tables,
 * and writes an additive migration for what is missing (--migration). Fails
 * on incompatible differences; never changes the database itself.
 */
final class SchemaDiffCommand extends Command
{
    protected $signature = 'icms:schema:diff
        {resource? : a resource, for example system.mimetype; all when omitted}
        {--migration : write an additive migration into the module\'s migrations directory}';

    protected $description = 'Compare the IPF schema contracts with the database';

    public function handle(ResourceRegistry $resources, DatabaseManager $db, MigrationGenerator $generator): int
    {
        $key = $this->argument('resource');
        $registrations = is_string($key) && $key !== '' ? [$resources->get($key)] : array_values($resources->all());
        $diff = new SchemaDiff($db->connection('icms'));

        $failed = false;
        foreach ($registrations as $registration) {
            /** @var class-string<SchemaContract> $contract */
            $contract = $registration->generatedClass('Schema');
            if (! class_exists($contract)) {
                $this->error("{$registration->key()}: not compiled; run `php artisan icms:definitions:compile`.");
                $failed = true;

                continue;
            }

            $differences = $diff->compare($contract);
            $this->line("<info>{$registration->key()}</info> ({$contract::table()})");
            if ($differences === []) {
                $this->line('  the table matches the definition');
            }

            foreach ($differences as $difference) {
                $this->line("  [{$difference->kind}] {$difference->subject}: {$difference->detail}");
                $failed = $failed || $difference->kind === SchemaDifference::INCOMPATIBLE;
            }

            $additive = array_filter($differences, static fn (SchemaDifference $difference): bool => $difference->kind === SchemaDifference::ADDITIVE);
            if ($additive !== [] && $this->option('migration')) {
                $directory = "{$registration->basePath}/migrations";
                if (! is_dir($directory)) {
                    mkdir($directory, 0777, true);
                }

                $file = $directory . '/' . date('Y_m_d_His') . "_expand_{$registration->module}_{$registration->item}.php";
                file_put_contents($file, $generator->generate($contract::table(), $differences));
                $this->line("  migration written: {$file}");
            } elseif ($additive !== []) {
                $this->line('  run with --migration to write an additive migration');
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
