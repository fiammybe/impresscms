<?php

declare(strict_types=1);

namespace App\Tests\Http\System;

use App\EventListener\Test\HostMarkerListener;
use App\Tests\Support\HttpTestCase;
use App\Tests\Support\TestSite;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\DomCrawler\Crawler;

/**
 * The preloads administration (App\Controller\System\PreloadController). A
 * probe preload, installed for this test class only, sends a header from its
 * startCoreBoot event, so tests can see whether the next request loads it.
 */
#[Group('spec:system-preloads-admin')]
final class PreloadAdminTest extends HttpTestCase
{
    private const PAGE = '/admin/preloads';
    private const PAGE_LEVEL_1 = '/index.php/admin/preloads';
    private const PROBE = 'zzicmsadminprobe';

    /** _CORE_TOKENINVALID */
    private const TOKEN_INVALID = 'No valid token found in request/session';

    public static function setUpBeforeClass(): void
    {
        file_put_contents(self::probeFile(), <<<'PHP'
            <?php
            // Test probe (tests/Http/System/PreloadAdminTest.php); removed after the test.
            class IcmsPreloadZzicmsadminprobe extends icms_preload_Item {
                public function eventStartCoreBoot() {
                    header('X-Test-Admin-Probe: loaded');
                }
            }
            PHP);

        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        @unlink(self::probeFile());
        self::removeStore();
    }

    protected function setUp(): void
    {
        TestSite::restoreSnapshot();
        TestSite::clearLegacyCaches();
        self::removeStore();
        parent::setUp();
    }

    /**
     * @scenario Clean URL
     * @scenario Webmaster
     */
    public function testCleanUrlServesThePage(): void
    {
        $this->loginAsWebmaster();
        $response = $this->request('GET', self::PAGE);

        self::assertSame(200, $response->getStatusCode(), self::serverLog());
        self::assertSame('system_preloads', $response->getHeader(HostMarkerListener::ROUTE_HEADER));
        self::assertStringContainsString('id="icms_preloads"', $response->getContent());
    }

    /**
     * @scenario Routing level 1
     */
    public function testIndexPhpUrlServesThePage(): void
    {
        $this->loginAsWebmaster();
        $response = $this->request('GET', self::PAGE_LEVEL_1);

        self::assertSame(200, $response->getStatusCode(), self::serverLog());
        self::assertSame('system_preloads', $response->getHeader(HostMarkerListener::ROUTE_HEADER));
        self::assertStringContainsString('action="/index.php/admin/preloads/toggle"', $response->getContent(), 'links keep the URL the request came in on');
    }

    /**
     * @scenario Old-style admin link
     */
    public function testAdminPhpLinkRedirectsToThePage(): void
    {
        $this->loginAsWebmaster();
        $response = $this->request('GET', '/modules/system/admin.php?fct=preloads');

        self::assertSame(302, $response->getStatusCode(), self::serverLog());
        self::assertStringEndsWith(self::PAGE_LEVEL_1, (string) $response->getHeader('Location'));
    }

    /**
     * @scenario Anonymous visitor
     */
    public function testAnonymousVisitorIsSentToTheLoginPage(): void
    {
        $response = $this->request('GET', self::PAGE);

        self::assertStringNotContainsString('id="icms_preloads"', $response->getContent());
        self::assertStringContainsString('user.php', $this->redirectTarget($response));
    }

    /**
     * @scenario Administrator without the preloads right
     */
    public function testAdministratorWithoutTheRightIsDenied(): void
    {
        $this->loginAs(TestSite::MEDIAADMIN_UNAME, TestSite::MEDIAADMIN_PASS);
        $response = $this->request('GET', self::PAGE);

        self::assertSame(302, $response->getStatusCode(), self::serverLog());
        self::assertStringEndsWith('/admin.php', (string) $response->getHeader('Location'));
    }

    /**
     * @scenario Right granted to a group
     */
    public function testGroupWithTheRightMayUseThePage(): void
    {
        $groupId = self::mediaadminGroup();
        $this->loginAsWebmaster();
        $groupForm = $this->request('GET', "/modules/system/admin.php?fct=groups&op=modify&g_id={$groupId}");
        self::assertStringContainsString('Preloads', $groupForm->getContent(), 'the group administration offers the right');

        self::grantPreloadsRight($groupId);
        $this->browser->getCookieJar()->clear();
        $this->loginAs(TestSite::MEDIAADMIN_UNAME, TestSite::MEDIAADMIN_PASS);
        $response = $this->request('GET', self::PAGE);

        self::assertSame(200, $response->getStatusCode(), self::serverLog());
        self::assertSame('system_preloads', $response->getHeader(HostMarkerListener::ROUTE_HEADER));
    }

    /**
     * @scenario Menu entry for a webmaster
     */
    public function testMenuLinksToThePageForAWebmaster(): void
    {
        $this->loginAsWebmaster();
        $response = $this->request('GET', '/modules/system/admin.php?fct=userrank');

        self::assertSame(200, $response->getStatusCode(), self::serverLog());
        self::assertStringContainsString('/index.php/admin/preloads"', $response->getContent());
    }

    /**
     * @scenario Menu entry hidden without the right
     */
    public function testMenuHasNoEntryWithoutTheRight(): void
    {
        $this->loginAs(TestSite::MEDIAADMIN_UNAME, TestSite::MEDIAADMIN_PASS);
        $response = $this->request('GET', '/modules/system/admin.php?fct=userrank');

        self::assertSame(200, $response->getStatusCode(), self::serverLog());
        self::assertStringNotContainsString('/index.php/admin/preloads', $response->getContent());
    }

    /**
     * @scenario Listing
     */
    public function testListsEveryInstalledPreloadWithItsEvents(): void
    {
        $this->loginAsWebmaster();
        $rows = $this->rows($this->browser->request('GET', static::url(self::PAGE)));

        $installed = array_map(static fn (string $file): string => basename($file, '.php'), glob(TestSite::legacyRoot() . '/plugins/preloads/*.php') ?: []);
        sort($installed, SORT_STRING | SORT_FLAG_CASE);
        self::assertSame($installed, array_keys($rows));
        self::assertSame(['enabled'], array_values(array_unique(array_column($rows, 'state'))));
        self::assertStringContainsString('beforeFooter', $rows['keepalive']['events']);
    }

    /**
     * @scenario Disabled preload in the list
     */
    public function testDisabledPreloadIsListedWithItsEventsWithoutRunning(): void
    {
        self::writeStore([self::PROBE]);
        $this->loginAsWebmaster();
        $crawler = $this->browser->request('GET', static::url(self::PAGE));
        $response = $this->browser->getInternalResponse();

        $rows = $this->rows($crawler);
        self::assertSame('disabled', $rows[self::PROBE]['state']);
        self::assertSame('startCoreBoot', $rows[self::PROBE]['events']);
        self::assertNull($response->getHeader('X-Test-Admin-Probe'));
    }

    /**
     * @scenario Disable a preload
     * @scenario Enable a preload
     */
    public function testDisableAndEnableAPreload(): void
    {
        $this->loginAsWebmaster();
        self::assertSame('loaded', $this->request('GET', '/index.php')->getHeader('X-Test-Admin-Probe'));

        $response = $this->submitToggle(self::PROBE);
        $this->assertRedirectReports($response, 'The preload ' . self::PROBE . ' is disabled.');
        self::assertSame([self::PROBE], self::storedNames());
        self::assertNull($this->request('GET', '/index.php')->getHeader('X-Test-Admin-Probe'));
        self::assertSame('disabled', $this->rows($this->browser->request('GET', static::url(self::PAGE)))[self::PROBE]['state']);

        $response = $this->submitToggle(self::PROBE);
        $this->assertRedirectReports($response, 'The preload ' . self::PROBE . ' is enabled.');
        self::assertSame([], self::storedNames());
        self::assertSame('loaded', $this->request('GET', '/index.php')->getHeader('X-Test-Admin-Probe'));
        self::assertSame('enabled', $this->rows($this->browser->request('GET', static::url(self::PAGE)))[self::PROBE]['state']);
    }

    /**
     * @scenario Unknown preload
     */
    public function testUnknownAndTraversalNamesChangeNothing(): void
    {
        $this->loginAsWebmaster();

        foreach (['../../mainfile', 'nosuchpreload'] as $name) {
            $response = $this->submitToggle(self::PROBE, ['preload' => $name]);

            $this->assertRedirectReports($response, 'There is no preload named');
            self::assertFileDoesNotExist(self::storeFile());
        }
    }

    /**
     * @scenario Missing token
     */
    public function testToggleWithoutATokenChangesNothing(): void
    {
        $this->loginAsWebmaster();
        $this->request('GET', self::PAGE);

        $response = $this->request('POST', self::PAGE . '/toggle', ['preload' => self::PROBE, 'state' => 'disable']);

        $this->assertRedirectReports($response, self::TOKEN_INVALID);
        self::assertFileDoesNotExist(self::storeFile());
    }

    /**
     * @scenario GET request
     */
    public function testGetOnTheToggleChangesNothing(): void
    {
        $this->loginAsWebmaster();
        $response = $this->request('GET', self::PAGE . '/toggle?preload=' . self::PROBE . '&state=disable');

        self::assertSame(405, $response->getStatusCode(), self::serverLog());
        self::assertFileDoesNotExist(self::storeFile());
    }

    /**
     * @scenario Read-only storage
     */
    public function testUnwritableStorageIsReported(): void
    {
        // a directory where the store file belongs: it cannot be replaced
        @mkdir(self::storeFile(), 0777, true);
        $this->loginAsWebmaster();

        $response = $this->submitToggle(self::PROBE);

        $this->assertRedirectReports($response, 'must be writable by the web server');
        self::assertSame('loaded', $this->request('GET', '/index.php')->getHeader('X-Test-Admin-Probe'));
    }

    public function testStoreIsNotServedOverHttp(): void
    {
        self::writeStore([self::PROBE]);

        foreach (['/../trust/var/preloads.php', '/trust/var/preloads.php', '/var/preloads.php'] as $path) {
            $response = $this->request('GET', $path);

            // unknown paths run the legacy index.php: the store's contents never appear
            self::assertStringNotContainsString(self::PROBE, $response->getContent(), $path);
        }
    }

    // -- Helpers -----------------------------------------------------------------

    /**
     * Post the toggle form of $name from the list, with $changes to its values.
     *
     * @param array<string, string> $changes
     */
    private function submitToggle(string $name, array $changes = []): Response
    {
        $crawler = $this->browser->request('GET', static::url(self::PAGE));
        $form = $crawler->filterXPath(sprintf('//tr[@data-preload="%s"]//form', $name))->form();
        $values = array_replace($form->getPhpValues(), $changes);

        return $this->request('POST', (string) parse_url($form->getUri(), PHP_URL_PATH), $values);
    }

    /**
     * @return array<string, array{state: string, events: string}>
     */
    private function rows(Crawler $crawler): array
    {
        $rows = [];
        $crawler->filterXPath('//table[@id="icms_preloads"]//tr[@data-preload]')->each(static function (Crawler $row) use (&$rows): void {
            $rows[(string) $row->attr('data-preload')] = [
                'state' => (string) $row->attr('data-state'),
                'events' => trim($row->filterXPath('//td[2]')->text()),
            ];
        });

        return $rows;
    }

    /**
     * The message of LegacyRedirect: on the redirect page, or with
     * use_custom_redirection on the page the redirect leads to.
     */
    private function assertRedirectReports(Response $response, string $message): void
    {
        if ($response->getStatusCode() === 200) {
            self::assertStringContainsString('http-equiv="Refresh"', $response->getContent(), 'the redirect page');
            self::assertStringContainsString($message, $response->getContent());

            return;
        }

        self::assertSame(302, $response->getStatusCode(), self::serverLog());
        $next = $this->request('GET', (string) $response->getHeader('Location'));
        self::assertStringContainsString($message, $next->getContent());
    }

    private function redirectTarget(Response $response): string
    {
        if ($response->getStatusCode() === 302) {
            return (string) $response->getHeader('Location');
        }

        return $response->getContent();
    }

    private static function mediaadminGroup(): int
    {
        $stmt = TestSite::connection()->prepare(
            'SELECT l.groupid FROM ' . TestSite::table('groups_users_link') . ' l JOIN ' . TestSite::table('users') . ' u ON u.uid = l.uid'
            . ' WHERE u.uname = ? AND l.groupid > 3 ORDER BY l.groupid LIMIT 1',
        );
        $stmt->execute([TestSite::MEDIAADMIN_UNAME]);

        return (int) $stmt->fetchColumn();
    }

    private static function grantPreloadsRight(int $groupId): void
    {
        TestSite::connection()->prepare(
            'INSERT INTO ' . TestSite::table('group_permission') . " (gperm_groupid, gperm_itemid, gperm_modid, gperm_name) VALUES (?, 25, 1, 'system_admin')",
        )->execute([$groupId]);
        TestSite::clearLegacyCaches();
    }

    /**
     * @param list<string> $names
     */
    private static function writeStore(array $names): void
    {
        @mkdir(dirname(self::storeFile()), 0777, true);
        file_put_contents(self::storeFile(), '<?php return ' . var_export(['disabled' => $names], true) . ';');
    }

    /** @return list<string> */
    private static function storedNames(): array
    {
        clearstatcache();

        return (require self::storeFile())['disabled'];
    }

    private static function removeStore(): void
    {
        $file = self::storeFile();
        if (is_dir($file)) {
            @rmdir($file);
        }
        @unlink($file);
        @unlink("{$file}.lock");
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
