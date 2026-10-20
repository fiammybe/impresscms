<?php

declare(strict_types=1);

namespace App\Tests\Http;

use App\Tests\Support\HttpTestCase;
use App\Tests\Support\TestSite;
use Icms\Core\Password;
use Icms\Core\Password\Legacy;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\BrowserKit\Response;

/**
 * The password login through the host (OpenSpec change symfony-password-login):
 * App\Security\Authenticator\Password with the hashers, the user checker and
 * the throttling.
 */
#[Group('spec:authentication')]
final class PasswordLoginTest extends HttpTestCase
{
    private const MEMBER_ID = 2;

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

    /**
     * @param array<string, string> $fields
     * @param array<string, string> $server
     */
    private function postLogin(array $fields, array $server = [], string $path = '/user.php'): Response
    {
        $this->request('GET', '/user.php');

        return $this->request('POST', $path, $fields + ['op' => 'login'], $server + ['HTTP_REFERER' => static::url('/user.php')]);
    }

    private function setPass(int $uid, string $pass): void
    {
        TestSite::connection()->prepare('UPDATE ' . TestSite::table('users') . ' SET pass = ? WHERE uid = ?')->execute([$pass, $uid]);
    }

    private function pass(int $uid): string
    {
        $statement = TestSite::connection()->prepare('SELECT pass FROM ' . TestSite::table('users') . ' WHERE uid = ?');
        $statement->execute([$uid]);

        return (string) $statement->fetchColumn();
    }

    /**
     * The message the next page shows (header.php, from $_SESSION['redirect_message']).
     */
    private function nextPageMessage(): string
    {
        return $this->request('GET', '/user.php')->getContent();
    }

    /**
     * @scenario Successful login
     */
    public function testLoginRedirectsToTheReturnUrlWithinTheSite(): void
    {
        $response = $this->postLogin(['uname' => TestSite::MEMBER_UNAME, 'pass' => TestSite::MEMBER_PASS, 'xoops_redirect' => '/search.php']);

        self::assertSame(302, $response->getStatusCode(), self::serverLog());
        self::assertSame(static::url('/search.php'), $response->getHeader('Location'));
        self::assertTrue($this->identity()['authenticated']);
    }

    /**
     * @scenario Successful login
     */
    public function testAReturnUrlOutsideTheSiteGoesToTheHomePage(): void
    {
        $response = $this->postLogin(['uname' => TestSite::MEMBER_UNAME, 'pass' => TestSite::MEMBER_PASS, 'xoops_redirect' => 'https://evil.example/']);

        self::assertSame(static::url('/'), $response->getHeader('Location'));
    }

    /**
     * @scenario Wrong password
     */
    public function testAWrongPasswordStaysAnonymous(): void
    {
        $response = $this->postLogin(['uname' => TestSite::MEMBER_UNAME, 'pass' => 'not-the-password']);

        self::assertSame(302, $response->getStatusCode(), self::serverLog());
        self::assertStringEndsWith('/user.php', (string) $response->getHeader('Location'));
        self::assertFalse($this->identity()['authenticated']);
        self::assertStringContainsString('Incorrect Login!', $this->nextPageMessage());
    }

    /**
     * @scenario Login with the e-mail address
     */
    public function testTheEmailAddressLogsIn(): void
    {
        $this->postLogin(['uname' => TestSite::MEMBER_EMAIL, 'pass' => TestSite::MEMBER_PASS]);

        self::assertSame(self::MEMBER_ID, $this->identity()['id']);
    }

    /**
     * @scenario Cross-site login post
     */
    public function testALoginPostedFromAnotherSiteIsRefused(): void
    {
        $this->postLogin(['uname' => TestSite::MEMBER_UNAME, 'pass' => TestSite::MEMBER_PASS], ['HTTP_ORIGIN' => 'https://evil.example']);
        self::assertFalse($this->identity()['authenticated']);

        $this->postLogin(['uname' => TestSite::MEMBER_UNAME, 'pass' => TestSite::MEMBER_PASS], ['HTTP_REFERER' => 'https://evil.example/form.html']);
        self::assertFalse($this->identity()['authenticated']);
    }

    /**
     * @scenario Expired password
     */
    public function testAnExpiredPasswordLeadsToTheResetForm(): void
    {
        TestSite::connection()->exec('UPDATE ' . TestSite::table('users') . ' SET pass_expired = 1 WHERE uid = ' . self::MEMBER_ID);

        $response = $this->postLogin(['uname' => TestSite::MEMBER_UNAME, 'pass' => TestSite::MEMBER_PASS]);

        self::assertStringEndsWith('/user.php?op=resetpass', (string) $response->getHeader('Location'));
        self::assertTrue($this->identity()['authenticated']);
    }

    /**
     * @scenario New password
     */
    public function testFixtureAccountsHaveModernHashes(): void
    {
        // bin/test-db creates the accounts with Icms\Core\Password::encryptPass()
        self::assertSame(Password::algorithm(), password_get_info($this->pass(self::MEMBER_ID))['algoName']);
        self::assertSame(Password::algorithm(), password_get_info(Password::getInstance()->encryptPass('x'))['algoName']);
        if (defined('PASSWORD_ARGON2ID') || extension_loaded('sodium')) {
            self::assertSame('argon2id', Password::algorithm(), 'Argon2id wherever PHP offers it');
        }
    }

    /**
     * @scenario Long password
     */
    public function testALongPasswordIsVerifiedInFull(): void
    {
        $long = str_repeat('long-password-', 4);
        $this->setPass(self::MEMBER_ID, Password::hasher()->hash($long));

        $this->postLogin(['uname' => TestSite::MEMBER_UNAME, 'pass' => substr($long, 0, 32)]);
        self::assertFalse($this->identity()['authenticated'], 'not cut to 32 characters');

        $this->postLogin(['uname' => TestSite::MEMBER_UNAME, 'pass' => $long]);
        self::assertTrue($this->identity()['authenticated']);
    }

    /**
     * @scenario Login upgrades the hash
     */
    public function testALoginReplacesAWrappedHashByAModernOne(): void
    {
        $legacy = Legacy::digest(TestSite::MEMBER_PASS, 's', 23, 5000, 'saltsaltsalt', TestSite::DB_SALT);
        $this->setPass(self::MEMBER_ID, Legacy::wrap((string) $legacy, Password::hasher()));

        $this->postLogin(['uname' => TestSite::MEMBER_UNAME, 'pass' => TestSite::MEMBER_PASS]);

        self::assertTrue($this->identity()['authenticated'], self::serverLog());
        self::assertNotNull(password_get_info($this->pass(self::MEMBER_ID))['algo'], 'a plain modern hash now');
        self::assertStringStartsNotWith(Legacy::WRAPPED, $this->pass(self::MEMBER_ID));
    }

    /**
     * @scenario Every legacy format verifies
     */
    public function testAnUnwrappedLegacyHashStillLogsIn(): void
    {
        // the salt-column format, as ImpressCMS 1.x stored it
        $this->setPass(self::MEMBER_ID, (string) Legacy::digest(TestSite::MEMBER_PASS, 'c', 3, 0, 'oldsalt', TestSite::DB_SALT));
        TestSite::connection()->exec('UPDATE ' . TestSite::table('users') . " SET salt = 'oldsalt', enc_type = 3 WHERE uid = " . self::MEMBER_ID);

        $this->postLogin(['uname' => TestSite::MEMBER_UNAME, 'pass' => TestSite::MEMBER_PASS]);

        self::assertTrue($this->identity()['authenticated'], self::serverLog());
        self::assertNotNull(password_get_info($this->pass(self::MEMBER_ID))['algo']);
    }

    /**
     * @scenario Not activated
     */
    public function testAnAccountThatIsNotActivatedCannotLogIn(): void
    {
        TestSite::connection()->exec('UPDATE ' . TestSite::table('users') . ' SET level = 0 WHERE uid = ' . self::MEMBER_ID);

        $this->postLogin(['uname' => TestSite::MEMBER_UNAME, 'pass' => TestSite::MEMBER_PASS]);

        self::assertFalse($this->identity()['authenticated']);
        self::assertStringContainsString('has not been activated yet', $this->nextPageMessage());
    }

    /**
     * @scenario Closed site
     */
    public function testOnlyAllowedGroupsLogInWhileTheSiteIsClosed(): void
    {
        TestSite::setConfig('closesite', '1');

        $this->request('POST', '/', ['xoops_login' => '1', 'uname' => TestSite::MEMBER_UNAME, 'pass' => TestSite::MEMBER_PASS], ['HTTP_REFERER' => static::url('/')]);
        self::assertFalse($this->identity()['authenticated'], 'members are not in closesite_okgrp');

        $this->request('POST', '/', ['xoops_login' => '1', 'uname' => TestSite::WEBMASTER_UNAME, 'pass' => TestSite::WEBMASTER_PASS], ['HTTP_REFERER' => static::url('/')]);
        self::assertSame(1, $this->identity()['id'], 'webmasters may always log in');
    }

    /**
     * @scenario Too many attempts
     */
    public function testFailedLoginsAreThrottled(): void
    {
        // a separate account, so that the limiter does not lock the fixture accounts
        $uname = 'throttled' . bin2hex(random_bytes(3));
        $pdo = TestSite::connection();
        $statement = $pdo->prepare('SELECT * FROM ' . TestSite::table('users') . ' WHERE uid = ?');
        $statement->execute([self::MEMBER_ID]);
        $row = ['uname' => $uname, 'login_name' => $uname, 'email' => $uname . '@example.test', 'pass' => Password::hasher()->hash('right-password')]
            + (array) $statement->fetch(\PDO::FETCH_ASSOC);
        unset($row['uid']);
        $pdo->prepare('INSERT INTO ' . TestSite::table('users') . ' (' . implode(', ', array_keys($row)) . ') VALUES (' . implode(', ', array_fill(0, count($row), '?')) . ')')
            ->execute(array_values($row));
        $pdo->prepare('INSERT INTO ' . TestSite::table('groups_users_link') . ' (groupid, uid) VALUES (2, ?)')->execute([(int) $pdo->lastInsertId()]);

        for ($i = 0; $i < 5; $i++) {
            $this->postLogin(['uname' => $uname, 'pass' => 'wrong-' . $i]);
        }
        $this->postLogin(['uname' => $uname, 'pass' => 'right-password']);

        self::assertFalse($this->identity()['authenticated'], 'refused, also with the right password');
        self::assertStringContainsString('Too many failed login attempts', $this->nextPageMessage());
    }
}
