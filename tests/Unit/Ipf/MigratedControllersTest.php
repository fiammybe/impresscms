<?php

declare(strict_types=1);

namespace App\Tests\Unit\Ipf;

use PHPUnit\Framework\TestCase;

/**
 * Migrated resource controllers stay on the Laravel side of the boundary:
 * no legacy globals, no handler lookups, no custom form objects, no
 * query-string dispatch. Legacy access belongs in App\Ipf adapters and the
 * modules' View\*LegacyViews.
 */
final class MigratedControllersTest extends TestCase
{
    private const FORBIDDEN = [
        'icms::' => 'legacy globals',
        'icms_getModuleHandler' => 'handler lookups',
        '$GLOBALS' => 'legacy globals',
        'IpfForm' => 'the removed form framework',
        'IpfField' => 'the removed form framework',
        "->get('op'" => 'query-string dispatch',
        '$_POST' => 'superglobals',
        '$_GET' => 'superglobals',
        '$_REQUEST' => 'superglobals',
    ];

    public function testControllersDoNotReachIntoLegacy(): void
    {
        $controllers = glob(dirname(__DIR__, 3) . '/app-modules/*/Http/*.php') ?: [];
        self::assertNotEmpty($controllers);

        foreach ($controllers as $file) {
            $code = (string) file_get_contents($file);
            foreach (self::FORBIDDEN as $needle => $what) {
                self::assertStringNotContainsString($needle, $code, basename($file) . " uses {$what}");
            }
        }
    }

    public function testGeneratedArtifactsAreMarked(): void
    {
        $generated = glob(dirname(__DIR__, 3) . '/app-modules/*/Generated/*/*.php') ?: [];
        self::assertNotEmpty($generated);

        foreach ($generated as $file) {
            self::assertStringContainsString('GENERATED FILE - DO NOT EDIT.', (string) file_get_contents($file), $file);
        }
    }
}
