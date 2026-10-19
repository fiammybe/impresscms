<?php

declare(strict_types=1);

namespace App\Tests\Http;

use App\Tests\Support\HttpTestCase;
use App\Tests\Support\TestSite;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\BrowserKit\Cookie;

/**
 * The host owns the session and the identity (OpenSpec change
 * symfony-owns-the-session): storage, the token as the identity, and the
 * legacy login and logout through the host.
 */
#[Group('spec:session-identity')]
final class SessionOwnershipTest extends HttpTestCase
{
    private const PRELOAD = 'zzicmstestlogin';

    public static function setUpBeforeClass(): void
    {
        file_put_contents(self::preloadFile(), <<<'PHP'
            <?php
            // Test preload (tests/Http/SessionOwnershipTest.php); removed after the test.
            icms_Event::attach('icms_core_Session', 'sessionStart', static function () {
                if (isset($_COOKIE['icms_test_preload_login'])) {
                    $_SESSION['xoopsUserId'] = 2;
                }
            });
            PHP);

        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        @unlink(self::preloadFile());
    }

    private static function preloadFile(): string
    {
        return TestSite::legacyRoot() . '/plugins/preloads/' . self::PRELOAD . '.php';
    }

    protected function setUp(): void
    {
        TestSite::restoreSnapshot();
        parent::setUp();
    }

    /**
     * @return array<string, mixed>
     */
    private function identity(): array
    {
        $response = $this->request('GET', '/_test/identity');
        self::assertSame(200, $response->getStatusCode(), $response->getContent() . self::serverLog());

        return json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    private function sessionCookie(): string
    {
        return (string) $this->browser->getCookieJar()->get('ICMSSESSION')?->getValue();
    }

    /**
     * @scenario Session stored in the session table
     */
    public function testTheSessionIsStoredInTheSessionTable(): void
    {
        $this->loginAsMember();

        $row = TestSite::connection()->prepare('SELECT sess_ip, sess_data FROM ' . TestSite::table('session') . ' WHERE sess_id = ?');
        $row->execute([$this->sessionCookie()]);
        $stored = $row->fetch(\PDO::FETCH_ASSOC);

        self::assertIsArray($stored, 'a row for the session cookie' . self::serverLog());
        self::assertSame('127.0.0.1', $stored['sess_ip']);
        self::assertStringContainsString('_security_main', $stored['sess_data'], 'the security token');
    }

    /**
     * @scenario Login regenerates the session
     */
    public function testLoginGivesANewSessionId(): void
    {
        $this->request('GET', '/user.php');
        $before = $this->sessionCookie();
        self::assertNotSame('', $before);

        $this->loginAsMember();

        self::assertNotSame($before, $this->sessionCookie());
        self::assertTrue($this->identity()['authenticated']);
    }

    /**
     * @scenario Identity keys are a mirror
     */
    public function testSessionKeysWithoutATokenAreAnonymous(): void
    {
        // a session the host stored (Symfony's metadata), in which some code wrote the key
        $created = time() - 600;
        $this->storeSession('_sf2_meta|a:3:{s:1:"u";i:' . $created . ';s:1:"c";i:' . $created . ';s:1:"l";i:0;}xoopsUserId|i:1;xoopsUserGroups|a:1:{i:0;i:1;}');

        $identity = $this->identity();
        self::assertFalse($identity['authenticated']);
        self::assertNull($identity['legacy_user_id'], 'not on legacy pages either');
        self::assertNull($identity['session_user_id'], 'the stale key is dropped');
        self::assertStringContainsString('name="uname"', $this->request('GET', '/user.php')->getContent(), 'the login form');
    }

    /**
     * @scenario Login from before the host
     */
    public function testALoginFromBeforeTheHostIsTakenOverOnce(): void
    {
        // as Icms\Core\Session stored it: no Symfony metadata, no token
        $this->storeSession('xoopsUserId|i:2;xoopsUserGroups|a:1:{i:0;i:2;}UserLanguage|s:7:"english";');

        $identity = $this->identity();
        self::assertSame(2, $identity['legacy_user_id'], 'taken over when legacy boots' . self::serverLog());
        $identity = $this->identity();
        self::assertTrue($identity['authenticated'], 'a token from then on');
        self::assertSame(2, $identity['id']);
    }

    private function storeSession(string $data): void
    {
        $id = 'icmstest' . bin2hex(random_bytes(10));
        TestSite::connection()
            ->prepare('INSERT INTO ' . TestSite::table('session') . ' (sess_id, sess_updated, sess_ip, sess_data) VALUES (?, ?, ?, ?)')
            ->execute([$id, time(), '127.0.0.1', $data]);
        $this->browser->getCookieJar()->set(new Cookie('ICMSSESSION', $id));
    }

    /**
     * @scenario Deactivated user
     */
    public function testADeactivatedUserIsAnonymousOnTheNextRequest(): void
    {
        $this->loginAsMember();
        self::assertTrue($this->identity()['authenticated']);

        TestSite::connection()->exec('UPDATE ' . TestSite::table('users') . ' SET level = 0 WHERE uid = 2');

        self::assertFalse($this->identity()['authenticated']);
    }

    /**
     * @scenario Login by a preload
     */
    public function testAPreloadThatSetsTheUserIdLogsTheUserIn(): void
    {
        $this->browser->getCookieJar()->set(new Cookie('icms_test_preload_login', '1'));
        $page = $this->request('GET', '/user.php');
        self::assertSame(302, $page->getStatusCode(), 'user.php sends a logged-in user to the profile' . self::serverLog());

        $this->browser->getCookieJar()->expire('icms_test_preload_login');
        $identity = $this->identity();
        self::assertTrue($identity['authenticated'], 'logged in for later requests');
        self::assertSame(2, $identity['id']);
    }
}
