<?php

declare(strict_types=1);

namespace App\Tests\Http;

use App\Tests\Support\HttpTestCase;
use App\Tests\Support\TestSite;
use Symfony\Component\BrowserKit\Response;

/**
 * The session keepalive of ImpressCMS 2.0.4 (htdocs/keepalive.php) on a session
 * the host owns: it answers a logged-in user, once a minute, and nobody else.
 */
final class KeepaliveTest extends HttpTestCase
{
    protected function setUp(): void
    {
        TestSite::restoreSnapshot();
        parent::setUp();
    }

    private function ping(): Response
    {
        return $this->request('GET', '/keepalive.php', [], [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_REFERER' => static::url('/user.php'),
        ]);
    }

    public function testALoggedInUserIsKeptAliveOncePerMinute(): void
    {
        TestSite::setConfig('keepalive_enable', '1');
        $this->loginAsWebmaster();

        $first = $this->ping();
        self::assertSame(200, $first->getStatusCode(), self::serverLog());
        self::assertSame('{"status":"ok"}', $first->getContent());

        $second = $this->ping();
        self::assertSame(429, $second->getStatusCode(), 'the session remembers the last ping');
        self::assertNotNull($second->getHeader('Retry-After'));
    }

    public function testNobodyIsKeptAliveAnonymouslyOrWhenDisabled(): void
    {
        TestSite::setConfig('keepalive_enable', '1');
        self::assertSame(403, $this->ping()->getStatusCode(), 'anonymous visitors');

        TestSite::setConfig('keepalive_enable', '0');
        $this->loginAsWebmaster();
        self::assertSame(403, $this->ping()->getStatusCode(), 'disabled by the preference');
    }

    public function testThePagesOfALoggedInUserLoadTheScriptWhenEnabled(): void
    {
        TestSite::setConfig('keepalive_enable', '1');
        $this->loginAsWebmaster();
        $this->request('GET', '/userinfo.php?uid=1');

        self::assertStringContainsString('id="keepalive-script"', $this->browser->getInternalResponse()->getContent());
    }
}
