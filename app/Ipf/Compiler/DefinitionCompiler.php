<?php

declare(strict_types=1);

namespace App\Ipf\Compiler;

use App\Ipf\Definition\DefinitionReader;
use App\Ipf\Definition\ResourceDefinition;
use App\Ipf\Resource\ResourceRegistration;
use App\Ipf\Resource\ResourceRegistry;
use App\Legacy\HandlerLocator;
use App\Legacy\LegacyRuntime;
use Illuminate\Container\Attributes\Config;
use RuntimeException;

/**
 * Reads the IPF definitions of the registered resources from the legacy
 * runtime and generates their Laravel artifacts (ArtifactGenerator).
 *
 * compile() writes them; check() compares them with the files on disk and
 * returns what differs (drift), without writing. Neither runs during a web
 * request: the host only loads the generated classes.
 */
final class DefinitionCompiler
{
    public function __construct(
        private readonly LegacyRuntime $runtime,
        private readonly HandlerLocator $handlers,
        private readonly ResourceRegistry $resources,
        private readonly ArtifactGenerator $generator,
        #[Config('icms.legacy.root')]
        private readonly string $legacyRoot,
    ) {
    }

    /**
     * The canonical definition of a registered resource.
     */
    public function definition(ResourceRegistration $registration): ResourceDefinition
    {
        $this->runtime->bootTo('module');
        $handler = $this->handlers->get($registration->handler);

        return (new DefinitionReader($this->legacyRoot, $this->tablePrefix()))->read($handler);
    }

    /**
     * @return array<string, string> absolute path => generated contents
     */
    public function artifacts(ResourceRegistration $registration): array
    {
        $files = [];
        foreach ($this->generator->generate($this->definition($registration), $registration) as $name => $contents) {
            $files["{$registration->generatedPath()}/{$name}"] = $contents;
        }

        return $files;
    }

    /**
     * Writes the artifacts of the resources (of one module, or all) and
     * removes generated files no longer produced.
     *
     * @return list<string> the files written or removed
     */
    public function compile(?string $module = null): array
    {
        $changed = [];
        foreach ($this->resources->all($module) as $registration) {
            $files = $this->artifacts($registration);
            $directory = $registration->generatedPath();
            if (! is_dir($directory) && ! mkdir($directory, 0777, true) && ! is_dir($directory)) {
                throw new RuntimeException("Cannot create {$directory}.");
            }

            foreach ($files as $path => $contents) {
                if (! is_file($path) || file_get_contents($path) !== $contents) {
                    file_put_contents($path, $contents);
                    $changed[] = $path;
                }
            }

            foreach ($this->stale($directory, $files) as $path) {
                unlink($path);
                $changed[] = $path;
            }
        }

        return $changed;
    }

    /**
     * The generated files that differ from the definitions: changed,
     * missing or no longer generated.
     *
     * @return array<string, string> path => what differs
     */
    public function check(?string $module = null): array
    {
        $drift = [];
        foreach ($this->resources->all($module) as $registration) {
            $files = $this->artifacts($registration);
            foreach ($files as $path => $contents) {
                if (! is_file($path)) {
                    $drift[$path] = 'missing';
                } elseif (file_get_contents($path) !== $contents) {
                    $drift[$path] = 'differs from the definition';
                }
            }

            foreach ($this->stale($registration->generatedPath(), $files) as $path) {
                $drift[$path] = 'no longer generated';
            }
        }

        ksort($drift);

        return $drift;
    }

    /**
     * @param array<string, string> $files
     * @return list<string>
     */
    private function stale(string $directory, array $files): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        $stale = [];
        foreach (glob("{$directory}/*.php") ?: [] as $path) {
            if (! isset($files[$path])) {
                $stale[] = $path;
            }
        }

        sort($stale);

        return $stale;
    }

    private function tablePrefix(): string
    {
        return defined('XOOPS_DB_PREFIX') ? XOOPS_DB_PREFIX . '_' : '';
    }
}
