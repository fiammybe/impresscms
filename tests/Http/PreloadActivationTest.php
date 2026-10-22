<?php

declare(strict_types=1);

namespace App\Tests\Http;

use App\Tests\Support\HttpTestCase;
use App\Tests\Support\TestSite;
use PHPUnit\Framework\Attributes\Group;

/**
 * Disabled preloads are not loaded, on legacy scripts and Symfony routes alike.
 * A probe preload, installed for this test class only, includes a marker header
 * from its file and from its startCoreBoot event; the disabled set is written
 * to the test site's trust path.
 */
#[Group('spec:preload-activation')]
final class PreloadActivationTest extends HttpTestCase
{
    private const PROBE = 'zzicmsactivationprobe';

    public static function setUpBeforeClass(): void
    {
        file_put_contents(self::probeFile(), <<<'PHP'
            <?php
            // Test probe (tests/Http/PreloadActivationTest.php); removed after the test.
            header('X-Test-Probe-File: included');
            class IcmsPreloadZzicmsactivationprobe extends icms_preload_Item {
                public function eventStartCoreBoot() {
                    header('X-Test-Probe-Event: startCoreBoot');
                }
            }
            PHP);

        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        @unlink(self::probeFile());
        @unlink(self::storeFile());
    }

    protected function tearDown(): void
    {
        @unlink(self::storeFile());

        parent::tearDown();
    }

    /**
     * @scenario No preload disabled
     */
    public function testEveryPreloadIsLoadedWithoutDisabledSet(): void
    {
        $response = $this->request('GET', '/index.php');

        self::assertSame(200, $response->getStatusCode(), self::serverLog());
        self::assertSame('included', $response->getHeader('X-Test-Probe-File'));
        self::assertSame('startCoreBoot', $response->getHeader('X-Test-Probe-Event'));
    }

    /**
     * @scenario Disabled preload
     */
    public function testDisabledPreloadIsNotIncludedOnALegacyPage(): void
    {
        self::writeStore([self::PROBE]);

        $response = $this->request('GET', '/index.php');

        self::assertSame(200, $response->getStatusCode(), self::serverLog());
        self::assertNull($response->getHeader('X-Test-Probe-File'));
        self::assertNull($response->getHeader('X-Test-Probe-Event'));
    }

    /**
     * @scenario Request served by the framework application
     */
    public function testDisabledPreloadIsNotLoadedForAFrameworkRoute(): void
    {
        $enabled = $this->request('GET', '/_test/legacy/sitename');
        self::assertSame(200, $enabled->getStatusCode(), self::serverLog());
        self::assertSame('startCoreBoot', $enabled->getHeader('X-Test-Probe-Event'), 'the probe is visible on a framework route');

        self::writeStore([self::PROBE]);
        $disabled = $this->request('GET', '/_test/legacy/sitename');

        self::assertSame(200, $disabled->getStatusCode(), self::serverLog());
        self::assertNull($disabled->getHeader('X-Test-Probe-File'));
        self::assertNull($disabled->getHeader('X-Test-Probe-Event'));
    }

    /**
     * @scenario Preload file updated
     */
    public function testDisabledPreloadStaysDisabledWhenItsFileIsReplaced(): void
    {
        self::writeStore([self::PROBE]);
        $original = (string) file_get_contents(self::probeFile());

        try {
            file_put_contents(self::probeFile(), str_replace('included', 'updated', $original));
            $response = $this->request('GET', '/index.php');
        } finally {
            file_put_contents(self::probeFile(), $original);
        }

        self::assertSame(200, $response->getStatusCode(), self::serverLog());
        self::assertNull($response->getHeader('X-Test-Probe-File'));
    }

    /**
     * @scenario Cache cleared
     */
    public function testDisabledPreloadStaysDisabledAfterClearingTheCaches(): void
    {
        self::writeStore([self::PROBE]);

        TestSite::clearLegacyCaches();
        self::assertStringNotContainsString('/cache/', self::storeFile(), 'the framework cache clear empties var/cache/<env> only');
        $response = $this->request('GET', '/index.php');

        self::assertSame(200, $response->getStatusCode(), self::serverLog());
        self::assertFileExists(self::storeFile());
        self::assertNull($response->getHeader('X-Test-Probe-Event'));
    }

    /**
     * @scenario Disabled preload removed from disk
     */
    public function testUnknownNamesAreIgnored(): void
    {
        self::writeStore(['oldplugin']);

        $response = $this->request('GET', '/index.php');

        self::assertSame(200, $response->getStatusCode(), self::serverLog());
        self::assertSame('startCoreBoot', $response->getHeader('X-Test-Probe-Event'));
    }

    /**
     * @scenario Corrupt disabled set
     */
    public function testCorruptStoreLoadsEveryPreload(): void
    {
        self::writeRawStore('<?php return [;');

        $response = $this->request('GET', '/index.php');

        self::assertSame(200, $response->getStatusCode(), self::serverLog());
        self::assertSame('startCoreBoot', $response->getHeader('X-Test-Probe-Event'));
    }

    /**
     * @param list<string> $names
     */
    private static function writeStore(array $names): void
    {
        self::writeRawStore('<?php return ' . var_export(['disabled' => $names], true) . ';');
    }

    private static function writeRawStore(string $code): void
    {
        @mkdir(dirname(self::storeFile()), 0777, true);
        file_put_contents(self::storeFile(), $code);

        if (function_exists('opcache_invalidate')) {
            opcache_invalidate(self::storeFile(), true);
        }
    }

    private static function storeFile(): string
    {
        return TestSite::trustPath() . '/var/preloads.php';
    }

    private static function probeFile(): string
    {
        return TestSite::legacyRoot() . '/plugins/preloads/' . self::PROBE . '.php';
    }
}
