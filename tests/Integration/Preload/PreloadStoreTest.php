<?php

declare(strict_types=1);

namespace App\Tests\Integration\Preload;

use App\Legacy\LegacyRuntime;
use App\Preload\DisabledPreloads;
use App\Preload\PreloadInventory;
use App\Preload\Preloads;
use App\Preload\PreloadStoreException;
use App\Tests\Support\LegacyWebTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * The disabled-preloads store and the preload inventory (design D3, D5),
 * against the library's reading side and the shipped preloads.
 */
#[RunTestsInSeparateProcesses]
#[Group('spec:preload-activation')]
final class PreloadStoreTest extends LegacyWebTestCase
{
    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        static::getContainer()->get(LegacyRuntime::class)->bootTo('constants');

        $this->dir = sys_get_temp_dir() . '/icms-preload-store-' . bin2hex(random_bytes(4));
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/{,.}*', GLOB_BRACE) ?: [] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        @chmod($this->dir . '/var', 0777);
        @rmdir($this->dir . '/var');
        @rmdir($this->dir);

        parent::tearDown();
    }

    public function testDisableAndEnableRoundTrip(): void
    {
        $store = new DisabledPreloads($this->dir . '/var/preloads.php');

        $store->disable('keepalive');
        $store->disable('debug_mode');
        $store->disable('keepalive');

        self::assertSame(['debug_mode', 'keepalive'], $store->names());
        self::assertSame(['debug_mode', 'keepalive'], \Icms\Preload\Handler::disabledPreloads($store->file()));

        $store->enable('keepalive');
        $store->enable('notdisabled');

        self::assertSame(['debug_mode'], $store->names());
        self::assertFalse($store->isDisabled('keepalive'));
    }

    /**
     * @scenario Disabled preload removed from disk
     */
    public function testInvalidAndTraversalNamesAreRejected(): void
    {
        $store = new DisabledPreloads($this->dir . '/preloads.php');

        foreach (['../../mainfile', 'a/b', '', 'x.php', "name\0"] as $name) {
            try {
                $store->disable($name);
                self::fail("'{$name}' was accepted");
            } catch (\InvalidArgumentException) {
            }
        }

        self::assertFileDoesNotExist($store->file());
    }

    public function testUnwritableDirectoryThrowsAndKeepsTheOldStore(): void
    {
        if (\PHP_OS_FAMILY === 'Windows') {
            $store = new DisabledPreloads($this->dir . '/missing/file/in/a/file/preloads.php');
            touch($this->dir . '/missing');
        }

        if (\PHP_OS_FAMILY !== 'Windows') {
            $previous = new DisabledPreloads($this->dir . '/var/preloads.php');
            $previous->disable('keepalive');
            chmod($this->dir . '/var', 0555);
            $store = new DisabledPreloads($this->dir . '/var/preloads.php');
        }

        try {
            $store->disable('debug_mode');
            self::fail('the store was written');
        } catch (PreloadStoreException $e) {
            self::assertStringContainsString('must be writable', $e->getMessage());
        }

        self::assertNotContains('debug_mode', $store->names());
    }

    public function testStoreWithoutTrustPathIsNotWritable(): void
    {
        $this->expectException(PreloadStoreException::class);

        (new DisabledPreloads(''))->disable('keepalive');
    }

    public function testSiteStoreLivesInTheTrustPath(): void
    {
        self::assertSame(ICMS_TRUST_PATH . '/var/preloads.php', $this->preloads()->disabled()->file());
    }

    public function testInventoryListsEveryShippedPreloadWithItsEvents(): void
    {
        $inventory = $this->preloads()->inventory();
        $names = array_map(static fn ($preload): string => $preload->name, $inventory->all());

        $shipped = array_map(
            static fn (string $file): string => basename($file, '.php'),
            glob(ICMS_PRELOAD_PATH . '/*.php') ?: [],
        );
        sort($shipped, SORT_STRING | SORT_FLAG_CASE);
        self::assertSame($shipped, $names);

        $keepalive = $inventory->find('keepalive');
        self::assertNotNull($keepalive);
        self::assertSame('IcmsPreloadKeepalive', $keepalive->className);
        self::assertSame(['beforeFooter', 'adminHeader'], $keepalive->events);
        self::assertTrue($keepalive->enabled);
        self::assertNull($inventory->find('nosuchpreload'));
    }

    public function testAttachedEventsAreListed(): void
    {
        $inventory = $this->preloads()->inventory();

        self::assertSame(['sessionStart (icms_core_Session)', 'sessionClose (icms_core_Session)'], $inventory->find('autologin')?->events);
        self::assertSame(['loadService', 'finishcoreboot', '*'], $inventory->find('bootstrap')?->events);
        self::assertSame([], $inventory->find('googleanalytics')?->events, 'its class is not IcmsPreloadGoogleanalytics, so the handler attaches nothing');
    }

    public function testParsedEventsEqualTheEventsTheHandlerAttaches(): void
    {
        foreach (glob(ICMS_PRELOAD_PATH . '/*.php') ?: [] as $file) {
            $name = basename($file, '.php');
            $className = PreloadInventory::className($name);
            $code = (string) file_get_contents($file);
            if (preg_match('/^\s*class\s+' . preg_quote($className, '/') . '\b/mi', $code) !== 1) {
                continue;
            }
            require_once $file;

            $attached = [];
            foreach (get_class_methods($className) as $method) {
                if (str_starts_with($method, 'event')) {
                    $attached[] = strtolower(str_replace('event', '', $method));
                }
            }

            $parsed = array_map('strtolower', PreloadInventory::events($code, $className));

            self::assertSame($attached, $parsed, $name);
        }
    }

    public function testEventsAreReadWithoutRunningTheFile(): void
    {
        $code = <<<'PHP'
            <?php
            throw new \LogicException('must not run');
            class IcmsPreloadProbe extends icms_preload_Item {
                private function eventHidden() {}
                protected static function eventAlsoHidden() {}
                public function eventBeforeFooter() { $x = "{$this->y}"; if (true) { return; } }
                function eventStartCoreBoot() {}
                public function helper() {}
                public static function eventFinishCoreBoot() {}
            }
            class Other { public function eventIgnored() {} }
            PHP;

        self::assertSame(
            ['beforeFooter', 'startCoreBoot', 'finishCoreBoot'],
            PreloadInventory::events($code, 'IcmsPreloadProbe'),
        );
        self::assertSame([], PreloadInventory::events('<?php // no class', 'IcmsPreloadProbe'));
    }

    private function preloads(): Preloads
    {
        return new Preloads(static::getContainer()->get(LegacyRuntime::class));
    }
}
