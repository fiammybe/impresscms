<?php

declare(strict_types=1);

namespace App\Tests\Http;

use App\Testing\HostMarker;
use App\Tests\Http\System\MimetypePage;
use App\Tests\Support\HttpTestCase;
use App\Tests\Support\ServerProcess;
use App\Tests\Support\TestSite;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Process\Process;

/**
 * Request ownership with the route table cached (`php artisan route:cache`):
 * takeovers by query string and POST body, other variants of the same legacy
 * script, methods (405), the fallback, reserved prefixes, static assets and
 * a switched-off takeover behave as without the cache.
 *
 * The cache is built before the server starts and cleared afterwards. The
 * whole Http suite can also run against cached routes (CI does).
 */
#[Group('spec:request-ownership')]
final class RouteCacheTest extends HttpTestCase
{
    use MimetypePage;

    private static bool $cachedByThisTest = false;

    public static function setUpBeforeClass(): void
    {
        if (ServerProcess::routing() === 3 && ! ServerProcess::forSite()->isExternal()) {
            self::$cachedByThisTest = ! is_file(self::cacheFile());
            self::artisan('route:cache');
        }

        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        if (self::$cachedByThisTest) {
            self::artisan('route:clear');
        }
    }

    protected function setUp(): void
    {
        if (ServerProcess::routing() !== 3) {
            self::markTestSkipped('Takeovers of .php URLs need routing level 3 (ICMS_TEST_ROUTING=3).');
        }

        if (ServerProcess::forSite()->isExternal()) {
            self::markTestSkipped('An external server has its own route cache.');
        }

        TestSite::restoreSnapshot();
        parent::setUp();
    }

    public function testTheRoutesAreCached(): void
    {
        self::assertFileExists(self::cacheFile());
    }

    public function testQueryAndPostVariantsAreTakenOver(): void
    {
        $this->asWebmaster();

        $list = $this->request('GET', self::PAGE);
        self::assertSame(200, $list->getStatusCode(), self::serverLog());
        self::assertSame(self::ROUTE, $list->getHeader(HostMarker::ROUTE_HEADER));

        // `fct` only in the POST body: the deletion confirmation, without a token
        $post = $this->request('POST', '/modules/system/admin.php', ['fct' => 'mimetype', 'op' => 'del', 'mimetypeid' => (string) self::ALPHA, 'confirm' => '1'], $this->referer());
        self::assertSame(self::ROUTE, $post->getHeader(HostMarker::ROUTE_HEADER));
        self::assertSame(302, $post->getStatusCode(), self::serverLog());
        self::assertNotNull(self::mimetypeRow('icmsa'), 'without a token nothing is deleted');

        // the POST body's `fct` replaces the query string's
        $replaced = $this->request('POST', '/modules/system/admin.php?fct=mimetype', ['fct' => 'userrank'], $this->referer());
        self::assertSame('icms.legacy', $replaced->getHeader(HostMarker::ROUTE_HEADER));
    }

    public function testOtherVariantsOfTheScriptStayLegacy(): void
    {
        $response = $this->asWebmaster()->request('GET', '/modules/system/admin.php?fct=userrank');

        self::assertSame(200, $response->getStatusCode(), self::serverLog());
        self::assertSame('icms.legacy', $response->getHeader(HostMarker::ROUTE_HEADER));
    }

    public function testMethodsOutsideTheTakeoverAre405OnlyForItsVariant(): void
    {
        $put = $this->request('PUT', self::PAGE);
        self::assertSame(405, $put->getStatusCode(), self::serverLog());
        self::assertSame('GET, HEAD, POST', $put->getHeader('Allow'));

        $legacy = $this->request('PUT', '/modules/system/admin.php?fct=userrank');
        self::assertNotSame(405, $legacy->getStatusCode(), 'other variants keep the legacy behaviour');

        self::assertSame(405, $this->request('POST', '/up')->getStatusCode());
        self::assertSame(200, $this->request('GET', '/up')->getStatusCode());
    }

    public function testFallbackReservedPrefixAndAssets(): void
    {
        $missing = $this->request('GET', '/does-not-exist');
        self::assertSame(200, $missing->getStatusCode(), self::serverLog());
        self::assertSame('icms.legacy', $missing->getHeader(HostMarker::ROUTE_HEADER));

        self::assertSame(404, $this->request('GET', '/admin-next/unknown')->getStatusCode());

        $asset = $this->request('GET', '/themes/iTheme/icons/icon.png');
        self::assertSame(200, $asset->getStatusCode());
        self::assertSame('image/png', $asset->getHeader('Content-Type'));
    }

    public function testASwitchedOffTakeoverIsLegacyWithCachedRoutes(): void
    {
        $server = ServerProcess::forSite(['ICMS_TAKEOVERS_DISABLED' => 'system.mimetype']);
        $server->start();

        try {
            $this->browser->request('GET', $server->baseUri() . '/user.php');
            $this->browser->request('POST', $server->baseUri() . '/user.php', ['uname' => TestSite::WEBMASTER_UNAME, 'pass' => TestSite::WEBMASTER_PASS, 'op' => 'login']);
            $this->browser->request('GET', $server->baseUri() . self::PAGE);
            $response = $this->browser->getInternalResponse();

            self::assertSame(200, $response->getStatusCode(), $server->log());
            self::assertSame('icms.legacy', $response->getHeader(HostMarker::ROUTE_HEADER));
        } finally {
            $server->stop();
        }
    }

    private static function cacheFile(): string
    {
        return TestSite::repoRoot() . '/bootstrap/cache/routes-v7.php';
    }

    private static function artisan(string $command): void
    {
        $process = new Process([PHP_BINARY, TestSite::repoRoot() . '/artisan', $command], TestSite::repoRoot(), [
            'APP_ENV' => 'test',
            'ICMS_MAINFILE' => TestSite::mainfile(),
        ]);
        $process->mustRun();
    }
}
