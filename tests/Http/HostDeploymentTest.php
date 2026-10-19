<?php

declare(strict_types=1);

namespace App\Tests\Http;

use App\EventListener\Test\HostMarkerListener;
use App\Tests\Support\HttpTestCase;
use App\Tests\Support\ServerProcess;
use App\Tests\Support\TestSite;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\HttpClient;

/**
 * The web root's index.php as the host's entry point: every request routed to
 * it (the test server, or an external server from docs/server/), and a server
 * that runs scripts itself (ServerProcess::notRouted()).
 */
#[Group('spec:host-deployment')]
final class HostDeploymentTest extends HttpTestCase
{
    protected function setUp(): void
    {
        TestSite::restoreSnapshot();
        parent::setUp();
    }

    /**
     * @scenario Home page through the host
     */
    public function testHomePageIsRenderedThroughTheHost(): void
    {
        $response = $this->request('GET', '/');

        self::assertSame(200, $response->getStatusCode(), self::serverLog());
        self::assertTrue(self::throughHost($response), 'index.php hands / to the host' . self::serverLog());
        self::assertStringContainsString(TestSite::SITE_NAME, $response->getContent());
    }

    /**
     * @scenario Trust path without a host
     */
    public function testWithoutAHostIndexPhpIsTheHomePage(): void
    {
        self::requireLocalServer();
        TestSite::removeTrustPathHost();
        try {
            $response = $this->request('GET', '/');
        } finally {
            TestSite::writeTrustPathHost();
        }

        self::assertSame(200, $response->getStatusCode(), self::serverLog());
        self::assertFalse(self::throughHost($response));
        self::assertStringContainsString(TestSite::SITE_NAME, $response->getContent());
    }

    /**
     * @scenario Routing level 3 without a host
     */
    public function testWithoutAHostIndexPhpRunsTheScriptARewrittenRequestAskedFor(): void
    {
        self::requireLocalServer();
        TestSite::removeTrustPathHost();
        try {
            $user = $this->request('GET', '/user.php');
            $templates = $this->request('GET', '/templates_c/index.html');
        } finally {
            TestSite::writeTrustPathHost();
        }

        self::assertSame(200, $user->getStatusCode(), self::serverLog());
        self::assertFalse(self::throughHost($user));
        self::assertStringContainsString('name="uname"', $user->getContent(), 'user.php, not the home page');
        self::assertStringNotContainsString('history.go', $templates->getContent(), 'never-served paths are not run');
    }

    /**
     * @scenario Symfony route through index.php
     */
    public function testSymfonyRouteThroughIndexPhp(): void
    {
        $response = $this->request('GET', '/up');

        self::assertSame(200, $response->getStatusCode(), self::serverLog());
        self::assertSame('ok', $response->getContent());
    }

    /**
     * @scenario Legacy script through the host
     */
    public function testLegacyScriptsRunThroughTheHost(): void
    {
        $response = $this->request('GET', '/user.php');

        self::assertSame(200, $response->getStatusCode(), self::serverLog());
        self::assertTrue(self::throughHost($response));
        // the same page as without the host; CharacterizationTest compares it in full
        self::assertStringContainsString('name="uname"', $response->getContent());
    }

    /**
     * @scenario Script executed by the web server itself
     */
    #[Group('spec:request-ownership')]
    public function testScriptsTheWebServerRunsItselfAreRefused(): void
    {
        self::requireLocalServer();
        $server = ServerProcess::notRouted();
        $server->start();
        $browser = new HttpBrowser(HttpClient::create(['timeout' => 30]));
        $browser->followRedirects(false);
        try {
            $browser->request('GET', $server->baseUri() . '/user.php');
            $user = $browser->getInternalResponse();
            $browser->request('GET', $server->baseUri() . '/');
            $home = $browser->getInternalResponse();

            TestSite::removeTrustPathHost();
            try {
                $browser->request('GET', $server->baseUri() . '/user.php');
                $withoutHost = $browser->getInternalResponse();
            } finally {
                TestSite::writeTrustPathHost();
            }
        } finally {
            $server->stop();
        }

        self::assertSame(503, $user->getStatusCode(), $server->log());
        self::assertStringContainsString('every PHP request to be sent to <code>index.php</code>', $user->getContent());
        self::assertStringNotContainsString('name="uname"', $user->getContent(), 'user.php did not run');
        self::assertSame(200, $home->getStatusCode(), 'index.php itself always reaches the host' . $server->log());
        self::assertTrue(self::throughHost($home));
        self::assertSame(200, $withoutHost->getStatusCode(), 'before the host is installed, scripts still run' . $server->log());
        self::assertStringContainsString('name="uname"', $withoutHost->getContent());
    }
    /**
     * @scenario Staging directory is not served
     */
    public function testStagingDirectoryIsNeverServed(): void
    {
        $staging = TestSite::legacyRoot() . '/trust_dist';
        if (is_dir($staging)) {
            self::markTestSkipped('htdocs/trust_dist exists (bin/build-dist); not touching it.');
        }

        $filesystem = new Filesystem();
        $filesystem->dumpFile($staging . '/composer.json', '{"name": "secret"}');
        $filesystem->dumpFile($staging . '/host.php', "<?php echo 'executed';\n");
        try {
            $json = $this->request('GET', '/trust_dist/composer.json');
            $php = $this->request('GET', '/trust_dist/host.php');
        } finally {
            $filesystem->remove($staging);
        }

        self::assertSame(404, $json->getStatusCode(), self::serverLog());
        self::assertStringNotContainsString('secret', $json->getContent());
        self::assertSame(404, $php->getStatusCode());
        self::assertStringNotContainsString('executed', $php->getContent());
    }

    /**
     * @scenario Supported web servers
     */
    public function testExampleConfigurationOfAWebServer(): void
    {
        if (ServerProcess::externalBaseUri() === null) {
            self::markTestSkipped('Runs against Apache, nginx and FrankenPHP in CI (ICMS_TEST_BASE_URL).');
        }

        self::assertTrue(self::throughHost($this->request('GET', '/')));
        self::assertSame('ok', $this->request('GET', '/up')->getContent());
        foreach (['/templates_c/index.html', '/cache/index.html', '/uploads/probe.php'] as $denied) {
            self::assertSame(404, $this->request('GET', $denied)->getStatusCode(), $denied);
        }
    }

    /**
     * @scenario Default trust path
     */
    public function testInstallerProposesATrustPathOutsideTheDocumentRoot(): void
    {
        self::requireLocalServer();

        $page = $this->request('GET', '/install/page_pathsettings.php');
        self::assertSame(200, $page->getStatusCode(), self::serverLog());
        self::assertMatchesRegularExpression('/name="TRUST_PATH" id="trustpath"\s+value="([^"]+)"/', $page->getContent());
        preg_match('/name="TRUST_PATH" id="trustpath"\s+value="([^"]+)"/', $page->getContent(), $m);

        $proposed = str_replace('\\', '/', $m[1]);
        self::assertSame(TestSite::repoRoot(), dirname($proposed), 'next to the document root htdocs/');
        self::assertStringNotContainsString('id="trustpublic"', $page->getContent(), 'no warning for the proposal');
    }

    /**
     * @scenario Default trust path
     */
    public function testInstallerWarnsAboutATrustPathInsideTheDocumentRoot(): void
    {
        self::requireLocalServer();

        $page = $this->request('POST', '/install/page_pathsettings.php', [
            'URL' => static::url(''),
            'ROOT_PATH' => TestSite::legacyRoot(),
            'TRUST_PATH' => TestSite::legacyRoot() . '/uploads',
        ]);
        self::assertContains($page->getStatusCode(), [302, 303], 'stays on the page' . self::serverLog());
        self::assertStringContainsString('page_pathsettings.php', (string) $page->getHeader('Location'));

        $page = $this->request('GET', '/install/page_pathsettings.php');
        self::assertStringContainsString('id="trustpublic"', $page->getContent());
        self::assertStringContainsString('name="TRUST_PATH_PUBLIC_OK"', $page->getContent());
    }

    private static function throughHost(Response $response): bool
    {
        return $response->getHeader(HostMarkerListener::HEADER) === '1';
    }

    private static function requireLocalServer(): void
    {
        if (ServerProcess::externalBaseUri() !== null) {
            self::markTestSkipped('Changes files of the test site; not against an external server.');
        }
    }
}
