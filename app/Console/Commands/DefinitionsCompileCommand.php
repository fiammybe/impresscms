<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Ipf\Compiler\DefinitionCompiler;
use Illuminate\Console\Command;

/**
 * Generates the Laravel artifacts of the registered IPF resources from their
 * legacy definitions.
 */
final class DefinitionsCompileCommand extends Command
{
    protected $signature = 'icms:definitions:compile {--module= : only the resources of this module}';

    protected $description = 'Generate the Laravel artifacts of the IPF resources from their definitions';

    public function handle(DefinitionCompiler $compiler): int
    {
        $module = $this->option('module');
        $changed = $compiler->compile(is_string($module) && $module !== '' ? $module : null);

        if ($changed === []) {
            $this->info('The generated artifacts are up to date.');

            return self::SUCCESS;
        }

        $base = str_replace('\\', '/', $this->laravel->basePath()) . '/';
        foreach ($changed as $path) {
            $this->line(' - ' . str_replace($base, '', str_replace('\\', '/', $path)));
        }

        $this->info(count($changed) . ' file(s) written or removed.');

        return self::SUCCESS;
    }
}
