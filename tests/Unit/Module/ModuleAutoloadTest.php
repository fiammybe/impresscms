<?php

declare(strict_types=1);

namespace App\Tests\Unit\Module;

use PHPUnit\Framework\TestCase;

/**
 * A module directory keeps its ImpressCMS dirname (`system`) while its
 * namespace is `Modules\System\`. On a case-sensitive filesystem only an
 * explicit psr-4 entry per module connects the two; Windows and macOS would
 * hide a missing one.
 */
final class ModuleAutoloadTest extends TestCase
{
    public function testEveryModuleDirectoryHasItsOwnPsr4Entry(): void
    {
        $root = dirname(__DIR__, 3);
        $composer = json_decode((string) file_get_contents("{$root}/composer.json"), true, flags: JSON_THROW_ON_ERROR);
        $psr4 = $composer['autoload']['psr-4'];

        self::assertArrayNotHasKey('Modules\\', $psr4, 'a catch-all Modules\\ entry only works on case-insensitive filesystems');

        $directories = array_map('basename', glob("{$root}/app-modules/*", GLOB_ONLYDIR) ?: []);
        self::assertNotEmpty($directories);

        foreach ($directories as $dirname) {
            $namespace = 'Modules\\' . ucfirst($dirname) . '\\';
            self::assertSame("app-modules/{$dirname}/", $psr4[$namespace] ?? null, "composer.json psr-4 entry for {$namespace}");
        }
    }
}
