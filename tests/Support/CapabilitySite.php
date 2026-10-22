<?php

declare(strict_types=1);

namespace App\Tests\Support;

use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * The capability fixture modules (tests/Fixtures/capability-site/modules) in
 * the test site: copied into htdocs/modules for a test class, installed
 * through the module administration's own functions, and removed again.
 *
 * - news: the consumer, IPF items article and category, no capability code
 * - smarttags, advancedtags: two competing tagging providers
 * - taxonomy: the taxonomy provider
 * - knowledgemanager: provides tagging and taxonomy
 * - oldtags (optional): a tagging provider of an incompatible contract version
 */
final class CapabilitySite
{
    public const MODULES = ['news', 'smarttags', 'advancedtags', 'taxonomy', 'knowledgemanager'];

    /** Fixture modules that only some tests install. */
    public const OPTIONAL_MODULES = ['oldtags'];

    public static function fixtureRoot(): string
    {
        return TestSite::repoRoot() . '/tests/Fixtures/capability-site/modules';
    }

    public static function moduleDir(string $dirname): string
    {
        return TestSite::legacyRoot() . '/modules/' . $dirname;
    }

    /** Copy the fixture modules into the site, replacing earlier copies. */
    public static function copyModules(): void
    {
        $filesystem = new Filesystem();

        foreach ([...self::MODULES, ...self::OPTIONAL_MODULES] as $dirname) {
            $target = self::moduleDir($dirname);
            $filesystem->remove($target);

            if (is_dir(self::fixtureRoot() . '/' . $dirname)) {
                $filesystem->mirror(self::fixtureRoot() . '/' . $dirname, $target);
            }
        }
    }

    public static function removeModules(): void
    {
        (new Filesystem())->remove(array_map(self::moduleDir(...), [...self::MODULES, ...self::OPTIONAL_MODULES]));
    }

    /** The registry snapshot of the test site (ICMS_CACHE_PATH, cleared with the legacy caches). */
    public static function snapshotFile(): string
    {
        return TestSite::legacyRoot() . '/cache/capability-registry.php';
    }

    /**
     * Run a module administration action for the modules in a separate
     * process, and return its result messages.
     */
    public static function admin(string $action, string ...$dirnames): string
    {
        $process = new Process(
            [PHP_BINARY, TestSite::repoRoot() . '/tests/Support/Install/capability-modules.php', TestSite::mainfile(), $action, ...$dirnames],
            TestSite::repoRoot(),
            ['ICMS_MAINFILE' => TestSite::mainfile()],
        );
        $process->setTimeout(120);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException("Module {$action} of " . implode(', ', $dirnames) . " failed:\n" . $process->getErrorOutput() . $process->getOutput());
        }

        return $process->getOutput();
    }

    public static function install(string ...$dirnames): string
    {
        return self::admin('install', ...$dirnames);
    }

    /**
     * Enable or disable capabilities for object types, as the capabilities
     * page stores them.
     *
     * @param array<string, array<string, bool>> $cells object type => capability => enabled
     */
    public static function assign(array $cells): void
    {
        $pdo = TestSite::connection();
        $table = TestSite::table('system_capabilityassignment');

        foreach ($cells as $objectType => $capabilities) {
            foreach ($capabilities as $capability => $enabled) {
                $pdo->prepare("DELETE FROM {$table} WHERE object_type = ? AND capability = ?")->execute([$objectType, $capability]);
                $pdo->prepare("INSERT INTO {$table} (object_type, capability, enabled, source) VALUES (?, ?, ?, 'admin')")
                    ->execute([$objectType, $capability, $enabled ? 1 : 0]);
            }
        }
    }

    /** Make the module the active provider, as the capabilities page stores it. */
    public static function select(string $capability, string $provider): void
    {
        $pdo = TestSite::connection();
        $table = TestSite::table('system_capabilityprovider');
        $pdo->prepare("DELETE FROM {$table} WHERE capability = ?")->execute([$capability]);
        $pdo->prepare("INSERT INTO {$table} (capability, provider, activated, activated_by) VALUES (?, ?, ?, 1)")
            ->execute([$capability, $provider, time()]);
    }

    /** @return array<string, string> capability => selected provider */
    public static function selections(): array
    {
        $rows = TestSite::connection()->query('SELECT capability, provider FROM ' . TestSite::table('system_capabilityprovider'));

        return $rows === false ? [] : $rows->fetchAll(\PDO::FETCH_KEY_PAIR);
    }
}
