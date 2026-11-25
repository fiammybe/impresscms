<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Ipf\Compiler\DefinitionCompiler;
use Illuminate\Console\Command;

/**
 * Fails when a generated artifact differs from what the IPF definitions
 * generate: edited by hand, or not regenerated after a definition changed.
 */
final class DefinitionsCheckCommand extends Command
{
    protected $signature = 'icms:definitions:check {--module= : only the resources of this module}';

    protected $description = 'Check that the generated IPF artifacts match their definitions';

    public function handle(DefinitionCompiler $compiler): int
    {
        $module = $this->option('module');
        $drift = $compiler->check(is_string($module) && $module !== '' ? $module : null);

        if ($drift === []) {
            $this->info('The generated artifacts match the IPF definitions.');

            return self::SUCCESS;
        }

        $base = str_replace('\\', '/', $this->laravel->basePath()) . '/';
        foreach ($drift as $path => $problem) {
            $this->error(' - ' . str_replace($base, '', str_replace('\\', '/', $path)) . ": {$problem}");
        }

        $this->line('Run `php artisan icms:definitions:compile` and commit the result; never edit generated files.');

        return self::FAILURE;
    }
}
