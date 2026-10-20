<?php

declare(strict_types=1);

namespace App\Tests\Http;

use App\Tests\Support\HttpTestCase;
use App\Tests\Support\TestSite;
use Icms\Core\Password;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Remember-me, login links and password reset by link (OpenSpec change
 * remember-me-and-email-links). The test environment writes mails to
 * var/test/mail (App\Mail\Transport\Directory). Every test uses an account
 * of its own, so that the mail rate limit (per address) never interferes.
 */
#[Group('spec:authentication')]
final class EmailLinkTest extends HttpTestCase
{
    private const PASSWORD = 'link-test-password';

    protected function setUp(): void
    {
        TestSite::restoreSnapshot();
        (new Filesystem())->remove(self::mailDir());
        parent::setUp();
    }

    private static function mailDir(): string
    {
        return TestSite::varDir() . '/mail';
    }

    /**
     * An active account with a unique address: [uid, login name, e-mail].
     *
     * @return array{int, string, string}
     */
    private function createAccount(): array
    {
        $name = 'link' . bin2hex(random_bytes(4));
        $pdo = TestSite::connection();
        $statement = $pdo->prepare('SELECT * FROM ' . TestSite::table('users') . ' WHERE uid = 2');
        $statement->execute();
        $row = ['uname' => $name, 'login_name' => $name, 'email' => $name . '@example.test', 'pass' => Password::hasher()->hash(self::PASSWORD)]
            + (array) $statement->fetch(\PDO::FETCH_ASSOC);
        unset($row['uid']);
        $pdo->prepare('INSERT INTO ' . TestSite::table('users') . ' (' . implode(', ', array_keys($row)) . ') VALUES (' . implode(', ', array_fill(0, count($row), '?')) . ')')
            ->execute(array_values($row));
        $uid = (int) $pdo->lastInsertId();
        $pdo->prepare('INSERT INTO ' . TestSite::table('groups_users_link') . ' (groupid, uid) VALUES (2, ?)')->execute([$uid]);

        return [$uid, $name, $name . '@example.test'];
    }

    private function requestMail(string $email, string $op): Response
    {
        $this->request('GET', '/user.php');

        return $this->request('POST', '/lostpass.php', ['email' => $email, 'op' => $op], ['HTTP_REFERER' => static::url('/user.php')]);
    }

    /**
     * The decoded text of the mails sent to $to.
     *
     * @return list<string>
     */
    private function mailsTo(string $to): array
    {
        $mails = [];
        foreach (glob(self::mailDir() . '/*.eml') ?: [] as $file) {
            $raw = (string) file_get_contents($file);
            [$headers, $body] = explode("\r\n\r\n", $raw, 2) + [1 => ''];
            if (preg_match('/^To: .*' . preg_quote($to, '/') . '/mi', $headers)) {
                $mails[] = str_contains($headers, 'quoted-printable') ? quoted_printable_decode($body) : $body;
            }
        }

        return $mails;
    }

    private function linkTo(string $to): string
    {
        $mails = $this->mailsTo($to);
        self::assertCount(1, $mails, 'one mail to ' . $to . self::serverLog());
        self::assertMatchesRegularExpression('#https?://\S+/auth/link/check\?\S+#', $mails[0]);
        preg_match('#https?://\S+/auth/link/check\?\S+#', $mails[0], $m);

        return $m[0];
    }

    /**
     * @return array<string, mixed>
     */
    private function identity(): array
    {
        return json_decode($this->request('GET', '/_test/identity')->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @scenario Login by link
     */
    public function testALoginLinkLogsInAfterTheConfirmation(): void
    {
        [$uid, , $email] = $this->createAccount();

        $response = $this->requestMail($email, 'loginlink');
        self::assertSame(302, $response->getStatusCode(), self::serverLog());
        self::assertStringEndsWith('/user.php', (string) $response->getHeader('Location'));

        $link = $this->linkTo($email);
        $this->browser->request('POST', $link);
        self::assertSame(302, $this->browser->getInternalResponse()->getStatusCode(), self::serverLog());
        self::assertSame($uid, $this->identity()['id']);
    }

    /**
     * @scenario Opening is not logging in
     */
    public function testOpeningALinkOnlyShowsTheConfirmation(): void
    {
        [$uid, , $email] = $this->createAccount();
        $this->requestMail($email, 'loginlink');
        $link = $this->linkTo($email);

        $page = $this->browser->request('GET', $link);
        self::assertSame(200, $this->browser->getInternalResponse()->getStatusCode(), self::serverLog());
        self::assertCount(1, $page->filterXPath('//div[@class="icms-auth-confirm"]/form[@method="post"]//input[@type="submit"]'), 'a confirmation button');
        self::assertStringContainsString(TestSite::SITE_NAME, $this->browser->getInternalResponse()->getContent(), 'inside the theme');
        self::assertFalse($this->identity()['authenticated']);

        $this->browser->submit($page->filterXPath('//div[@class="icms-auth-confirm"]/form')->form());
        self::assertSame($uid, $this->identity()['id'], 'the link still works for the confirmation');
    }

    /**
     * @scenario A link works once
     */
    public function testALinkWorksOnce(): void
    {
        [, , $email] = $this->createAccount();
        $this->requestMail($email, 'loginlink');
        $link = $this->linkTo($email);
        $this->browser->request('POST', $link);
        self::assertTrue($this->identity()['authenticated']);

        $this->browser->getCookieJar()->clear();
        $this->browser->request('POST', $link);
        self::assertFalse($this->identity()['authenticated']);
    }

    /**
     * @scenario Unknown address
     */
    public function testAnUnknownAddressGetsTheSameAnswerAndOnlyARegistrationLink(): void
    {
        [, , $known] = $this->createAccount();
        $knownResponse = $this->requestMail($known, 'loginlink');
        $unknown = 'nobody' . bin2hex(random_bytes(4)) . '@example.test';
        $unknownResponse = $this->requestMail($unknown, 'loginlink');

        self::assertSame($knownResponse->getStatusCode(), $unknownResponse->getStatusCode());
        self::assertSame($knownResponse->getHeader('Location'), $unknownResponse->getHeader('Location'));
        $mails = $this->mailsTo($unknown);
        self::assertCount(1, $mails, self::serverLog());
        self::assertStringContainsString('/register.php', $mails[0]);
        self::assertStringNotContainsString('/auth/link', $mails[0]);
    }

    /**
     * @scenario Reset by link
     * @scenario No password by mail
     */
    public function testAPasswordIsResetThroughALink(): void
    {
        [$uid, $name, $email] = $this->createAccount();
        $this->requestMail($email, 'mailpasswd');

        $mails = $this->mailsTo($email);
        self::assertCount(1, $mails, self::serverLog());
        self::assertStringNotContainsString(self::PASSWORD, $mails[0], 'no password by mail');
        $link = $this->linkTo($email);
        self::assertStringContainsString('reset=1', $link);

        $this->browser->request('POST', $link);
        self::assertStringEndsWith('/auth/password', (string) $this->browser->getInternalResponse()->getHeader('Location'), self::serverLog());
        $form = $this->browser->request('GET', static::url('/auth/password'));
        self::assertSame(200, $this->browser->getInternalResponse()->getStatusCode(), self::serverLog());
        $passwordForm = $form->filterXPath('//div[@class="icms-auth-password"]/form');
        self::assertCount(1, $passwordForm, $this->browser->getInternalResponse()->getContent());
        $this->browser->submit($passwordForm->form(),['password' => 'a-new-password', 'password2' => 'a-new-password']);
        self::assertStringEndsWith('/user.php', (string) $this->browser->getInternalResponse()->getHeader('Location'), self::serverLog());

        // the new password logs in, and the link is spent
        $this->browser->getCookieJar()->clear();
        self::assertSame(302, $this->loginAs($name, 'a-new-password')->getStatusCode());
        self::assertSame($uid, $this->identity()['id']);
        $this->browser->getCookieJar()->clear();
        $this->browser->request('POST', $link);
        self::assertFalse($this->identity()['authenticated'], 'the link stopped working with the new password');
    }

    /**
     * @scenario Remembered login
     */
    public function testARememberedUserIsLoggedInAgainWithARotatedToken(): void
    {
        TestSite::setConfig('remember_me', '1');
        [$uid, $name] = $this->createAccount();
        $this->request('GET', '/user.php');
        $this->request('POST', '/user.php', ['op' => 'login', 'uname' => $name, 'pass' => self::PASSWORD, 'rememberme' => 'On'], ['HTTP_REFERER' => static::url('/user.php')]);
        self::assertNotNull($this->browser->getCookieJar()->get('ICMSREMEMBERME'), 'a remember-me cookie' . self::serverLog());
        $before = $this->token($name);
        self::assertNotNull($before);

        // the session ends; the token was last used a while ago
        $this->browser->getCookieJar()->expire('ICMSSESSION');
        TestSite::connection()->prepare('UPDATE ' . TestSite::table('rememberme_token') . ' SET lastUsed = ? WHERE username = ?')
            ->execute([date('Y-m-d H:i:s', time() - 120), $name]);

        // a legacy page knows the user again (user.php sends members to their profile)
        $response = $this->request('GET', '/user.php');
        self::assertSame(302, $response->getStatusCode(), self::serverLog());
        self::assertStringContainsString('userinfo.php?uid=' . $uid, (string) $response->getHeader('Location'));
        self::assertSame($uid, $this->identity()['id'], self::serverLog());
        self::assertNotSame($before, $this->token($name), 'the token rotated');
    }

    /**
     * @scenario Logout forgets the user
     */
    public function testLogoutForgetsARememberedUser(): void
    {
        TestSite::setConfig('remember_me', '1');
        [, $name] = $this->createAccount();
        $this->request('GET', '/user.php');
        $this->request('POST', '/user.php', ['op' => 'login', 'uname' => $name, 'pass' => self::PASSWORD, 'rememberme' => 'On'], ['HTTP_REFERER' => static::url('/user.php')]);
        $cookie = $this->browser->getCookieJar()->get('ICMSREMEMBERME');
        self::assertNotNull($cookie, self::serverLog());

        $this->request('GET', '/user.php?op=logout');

        self::assertNull($this->token($name), 'the token is deleted');
        $this->browser->getCookieJar()->clear();
        $this->browser->getCookieJar()->set($cookie);
        self::assertFalse($this->identity()['authenticated'], 'the old cookie logs nobody in');
    }

    private function token(string $username): ?string
    {
        $statement = TestSite::connection()->prepare('SELECT value FROM ' . TestSite::table('rememberme_token') . ' WHERE username = ?');
        $statement->execute([$username]);
        $value = $statement->fetchColumn();

        return $value === false ? null : (string) $value;
    }
}
