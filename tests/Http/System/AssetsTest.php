<?php

declare(strict_types=1);

namespace App\Tests\Http\System;

use App\Controller\System\AssetsController;
use App\EventListener\Test\HostMarkerListener;
use App\Tests\Support\HttpTestCase;
use App\Tests\Support\ServerProcess;
use App\Tests\Support\TestSite;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\DomCrawler\Crawler;

/**
 * The asset manager (`admin.php?fct=assets`) end to end: access, the tree of
 * the upload folder, and deleting a file with confirmation, warning and token.
 *
 * Files are created below `uploads/assets-test/` (and, for the references,
 * in the image manager and rank folders) and removed after each test.
 */
#[Group('spec:system-assets-admin')]
final class AssetsTest extends HttpTestCase
{
    private const PAGE = '/modules/system/admin.php?fct=assets';
    private const FIXTURE_DIR = 'assets-test';

    /** _CORE_TOKENINVALID */
    private const TOKEN_INVALID = 'No valid token found in request/session';

    /** @var list<string> files and folders created by the test, removed in tearDown */
    private array $created = [];

    protected function setUp(): void
    {
        // admin.php only reaches the host when every .php request does
        if (ServerProcess::routing() !== 3) {
            self::markTestSkipped('The asset manager needs routing level 3 (ICMS_TEST_ROUTING=3).');
        }
        TestSite::restoreSnapshot();
        parent::setUp();

        $this->createFile(self::FIXTURE_DIR . '/assets-zeta.txt');
        $this->createFile(self::FIXTURE_DIR . '/assets-a&b.txt');
        $this->createFile(self::FIXTURE_DIR . '/index.html');
        $this->createFile(self::FIXTURE_DIR . '/assets-alpha/assets-beta.txt');
        $this->createFolder(self::FIXTURE_DIR . '/assets-empty');
    }

    protected function tearDown(): void
    {
        foreach (array_reverse($this->created) as $path) {
            if (is_link($path) || is_file($path)) {
                @unlink($path);
            } elseif (is_dir($path)) {
                @rmdir($path);
            }
        }

        parent::tearDown();
    }

    // -- Access --------------------------------------------------------------------

    /**
     * @scenario Open the page
     */
    public function testPageIsShownInTheAdminTheme(): void
    {
        $response = $this->asWebmaster()->request('GET', self::PAGE);

        self::assertSame(200, $response->getStatusCode(), self::serverLog());
        self::assertSame(AssetsController::ROUTE, $response->getHeader(HostMarkerListener::ROUTE_HEADER));
        self::assertStringContainsString('Asset manager', $response->getContent());
        self::assertStringContainsString('id="icms_folder_tree"', $response->getContent());
    }

    /**
     * @scenario Menu entry
     */
    public function testMenuListsTheAssetManager(): void
    {
        $response = $this->asWebmaster()->request('GET', self::PAGE);

        self::assertStringContainsString('admin.php?fct=mimetype', $response->getContent(), 'The system administration menu is not shown.');
        self::assertStringContainsString('admin.php?fct=assets', $response->getContent());
        self::assertStringContainsString('assets_big.png', $response->getContent());
    }

    /**
     * @scenario Administrator without the right
     */
    public function testAdministratorWithoutTheRightIsSentToTheAdminStartPage(): void
    {
        $this->loginAs(TestSite::MEDIAADMIN_UNAME, TestSite::MEDIAADMIN_PASS);
        $response = $this->request('GET', self::PAGE);

        self::assertSame(302, $response->getStatusCode(), self::serverLog());
        self::assertStringEndsWith('/admin.php', (string) $response->getHeader('Location'));
    }

    /**
     * @scenario Anonymous visitor
     */
    public function testAnonymousVisitorGetsTheDenial(): void
    {
        $response = $this->request('GET', self::PAGE);

        self::assertStringNotContainsString('icms_folder_tree', (string) $response->getContent());
        self::assertContains($response->getStatusCode(), [200, 302], self::serverLog());
    }

    /**
     * @scenario Granted administrator
     */
    public function testGrantedAdministratorSeesThePage(): void
    {
        $this->grantAssetsRight(TestSite::MIMETYPEADMIN_UNAME);

        $this->loginAs(TestSite::MIMETYPEADMIN_UNAME, TestSite::MIMETYPEADMIN_PASS);
        $response = $this->request('GET', self::PAGE);

        self::assertSame(200, $response->getStatusCode(), self::serverLog());
        self::assertStringContainsString('id="icms_folder_tree"', $response->getContent());
    }

    // -- The tree ------------------------------------------------------------------

    /**
     * @scenario Files and folders
     */
    public function testFoldersComeBeforeFilesAndEachLevelIsIndented(): void
    {
        $html = $this->asWebmaster()->request('GET', self::PAGE)->getContent();

        $order = ['assets-alpha', 'assets-beta.txt', 'assets-empty', 'assets-a&amp;b.txt', 'assets-zeta.txt'];
        $positions = array_map(static fn (string $name): int|false => strpos($html, $name), $order);
        self::assertNotContains(false, $positions, 'Not every fixture is listed.');
        $sorted = $positions;
        sort($sorted);
        self::assertSame($sorted, $positions, 'The tree is not in folder-first order.');

        self::assertStringContainsString('----&nbsp;assets-beta.txt', $html, 'A file two levels down is indented by level.');
        self::assertStringContainsString('assets-empty/', $html, 'An empty folder is listed.');
    }

    /**
     * @scenario Names are shown as text
     */
    public function testNamesAreEscaped(): void
    {
        $html = $this->asWebmaster()->request('GET', self::PAGE)->getContent();

        self::assertStringContainsString('assets-a&amp;b.txt', $html);
        self::assertStringNotContainsString('assets-a&b.txt', $html);
    }

    /**
     * @scenario Link leaving the upload folder
     */
    public function testLinkLeavingTheUploadFolderIsNotListed(): void
    {
        $outside = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'assets-outside-' . bin2hex(random_bytes(4));
        mkdir($outside);
        file_put_contents($outside . DIRECTORY_SEPARATOR . 'assets-secret.txt', 'secret');
        $link = $this->path(self::FIXTURE_DIR . '/assets-link');

        try {
            if (!@symlink($outside, $link)) {
                self::markTestSkipped('Symbolic links cannot be created here.');
            }
            $this->created[] = $link;

            $html = $this->asWebmaster()->request('GET', self::PAGE)->getContent();

            self::assertStringNotContainsString('assets-link', $html);
            self::assertStringNotContainsString('assets-secret.txt', $html);
        } finally {
            @unlink($outside . DIRECTORY_SEPARATOR . 'assets-secret.txt');
            @rmdir($outside);
        }
    }

    // -- Actions -------------------------------------------------------------------

    /**
     * @scenario File row
     * @scenario Folder row
     * @scenario Protected file in the tree
     */
    public function testOnlyUnprotectedFilesHaveADeleteAction(): void
    {
        $crawler = $this->asWebmaster()->openPage(self::PAGE);

        self::assertCount(1, $crawler->filterXPath($this->deleteLinks(self::FIXTURE_DIR . '/assets-zeta.txt')));
        self::assertCount(1, $crawler->filterXPath($this->deleteLinks(self::FIXTURE_DIR . '/assets-alpha/assets-beta.txt')));
        self::assertCount(0, $crawler->filterXPath($this->deleteLinks(self::FIXTURE_DIR . '/assets-alpha')), 'A folder has no action.');
        self::assertCount(0, $crawler->filterXPath($this->deleteLinks(self::FIXTURE_DIR . '/assets-empty')), 'A folder has no action.');
        self::assertCount(0, $crawler->filterXPath($this->deleteLinks(self::FIXTURE_DIR . '/index.html')), 'index.html is protected.');
        self::assertCount(0, $crawler->filterXPath($this->deleteLinks('blank.gif')), 'blank.gif is protected.');
    }

    // -- Deleting ------------------------------------------------------------------

    /**
     * @scenario Confirmation
     * @scenario Unreferenced file
     */
    public function testConfirmationNamesTheFileAndDeletesNothing(): void
    {
        $file = self::FIXTURE_DIR . '/assets-zeta.txt';

        [$crawler, $response] = $this->asWebmaster()->openConfirmation($file);

        self::assertSame(200, $response->getStatusCode(), self::serverLog());
        self::assertStringContainsString($file, $crawler->filterXPath('//div[@class="confirmMsg"]')->text());
        self::assertStringNotContainsString('still in use', $crawler->filterXPath('//div[@class="confirmMsg"]')->text());
        self::assertFileExists($this->path($file));
    }

    /**
     * @return array<string, array{string, string, string}> kind, file, SQL with the file name below its folder
     */
    public static function referencedFiles(): array
    {
        $fixture = self::FIXTURE_DIR;

        return [
            'user avatar' => ['a user avatar', $fixture . '/avatar-user.gif', "UPDATE %users% SET user_avatar = '$fixture/avatar-user.gif' WHERE uname = 'member'"],
            'avatar list' => ['an avatar in the avatar list', $fixture . '/avatar-list.gif', "INSERT INTO %avatar% (avatar_file, avatar_name) VALUES ('$fixture/avatar-list.gif', 'x')"],
            'smilie' => ['a smilie', $fixture . '/smilie.gif', "INSERT INTO %smiles% (code, smile_url, emotion) VALUES (':assets:', '$fixture/smilie.gif', 'x')"],
            'image manager' => ['an image of the image manager', 'imagemanager/assets-test.png', "INSERT INTO %image% (image_name, image_nicename) VALUES ('assets-test.png', 'x')"],
            'user rank' => ['a user rank image', 'system/userrank/assets-test.png', "INSERT INTO %ranks% (rank_title, rank_image) VALUES ('x', 'assets-test.png')"],
        ];
    }

    /**
     * @scenario Referenced file
     */
    #[DataProvider('referencedFiles')]
    public function testConfirmationWarnsAboutReferences(string $kind, string $file, string $sql): void
    {
        $this->createFile($file);
        $this->execute($sql);

        [$crawler, $response] = $this->asWebmaster()->openConfirmation($file);

        self::assertSame(200, $response->getStatusCode(), self::serverLog());
        self::assertStringContainsString('still in use as ' . $kind, $crawler->filterXPath('//div[@class="confirmMsg"]')->text());
        self::assertFileExists($this->path($file));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function unselectableFiles(): array
    {
        return [
            'unknown file' => [self::FIXTURE_DIR . '/assets-nope.txt'],
            'a folder' => [self::FIXTURE_DIR . '/assets-alpha'],
            'parent folder' => ['../index.php'],
            'empty path' => [''],
            'absolute path' => [dirname(__DIR__, 3) . '/htdocs/index.php'],
        ];
    }

    /**
     * @scenario Unknown file
     * @scenario Path outside the upload folder
     */
    #[DataProvider('unselectableFiles')]
    public function testUnselectableFileIsNotSelected(string $path): void
    {
        $this->withoutCustomRedirection()->asWebmaster();

        $response = $this->request('GET', self::PAGE . '&op=del&path=' . rawurlencode($path), [], $this->referer());

        self::assertStringContainsString('No file selected.', $this->message($response));
        self::assertFileExists(TestSite::legacyRoot() . '/index.php');
    }

    /**
     * @scenario Delete request for a protected file
     */
    public function testProtectedFileIsNotDeletedEvenWithAToken(): void
    {
        $this->withoutCustomRedirection()->asWebmaster();
        $values = $this->confirmationValues(self::FIXTURE_DIR . '/assets-zeta.txt');
        $values['path'] = self::FIXTURE_DIR . '/index.html';

        $response = $this->request('POST', '/modules/system/admin.php', $values, $this->referer());

        self::assertStringContainsString('This file is protected', $this->message($response));
        self::assertFileExists($this->path(self::FIXTURE_DIR . '/index.html'));
        self::assertFileExists($this->path(self::FIXTURE_DIR . '/assets-zeta.txt'));
    }

    /**
     * @scenario Confirmed delete
     * @scenario Referenced file is deleted
     */
    public function testConfirmedDeleteRemovesTheFileAndKeepsTheRows(): void
    {
        $file = self::FIXTURE_DIR . '/avatar-user.gif';
        $this->createFile($file);
        $this->execute("UPDATE %users% SET user_avatar = '$file' WHERE uname = 'member'");
        $this->withoutCustomRedirection()->asWebmaster();

        $response = $this->request('POST', '/modules/system/admin.php', $this->confirmationValues($file), $this->referer());

        self::assertStringContainsString('The file was deleted.', $this->message($response));
        self::assertFileDoesNotExist($this->path($file));
        self::assertSame($file, $this->query("SELECT user_avatar FROM %users% WHERE uname = 'member'"));

        $html = $this->request('GET', self::PAGE)->getContent();
        self::assertStringNotContainsString('avatar-user.gif', $html);
    }

    /**
     * @scenario Missing or replayed token
     */
    public function testMissingTokenDeletesNothing(): void
    {
        $file = self::FIXTURE_DIR . '/assets-zeta.txt';
        $this->withoutCustomRedirection()->asWebmaster();
        $values = $this->confirmationValues($file);
        unset($values['XOOPS_TOKEN_REQUEST']);

        $response = $this->request('POST', '/modules/system/admin.php', $values, $this->referer());

        self::assertStringContainsString(self::TOKEN_INVALID, $this->message($response));
        self::assertFileExists($this->path($file));
    }

    /**
     * @scenario Missing or replayed token
     */
    public function testReplayedTokenDeletesNothing(): void
    {
        $this->withoutCustomRedirection()->asWebmaster();
        $values = $this->confirmationValues(self::FIXTURE_DIR . '/assets-zeta.txt');

        $this->request('POST', '/modules/system/admin.php', $values, $this->referer());
        self::assertFileDoesNotExist($this->path(self::FIXTURE_DIR . '/assets-zeta.txt'), 'The first, valid confirmation did not delete.');

        $values['path'] = self::FIXTURE_DIR . '/assets-a&b.txt';
        $response = $this->request('POST', '/modules/system/admin.php', $values, $this->referer());

        self::assertStringContainsString(self::TOKEN_INVALID, $this->message($response));
        self::assertFileExists($this->path(self::FIXTURE_DIR . '/assets-a&b.txt'));
    }

    /**
     * @scenario Delete by link
     */
    public function testGetWithTheConfirmationFlagDeletesNothing(): void
    {
        $file = self::FIXTURE_DIR . '/assets-zeta.txt';
        $this->asWebmaster();

        $response = $this->request('GET', self::PAGE . '&op=del&confirm=1&path=' . rawurlencode($file), [], $this->referer());

        self::assertSame(200, $response->getStatusCode(), self::serverLog());
        self::assertStringContainsString('class="confirmMsg"', $response->getContent());
        self::assertFileExists($this->path($file));
    }

    /**
     * @scenario File gone in the meantime
     */
    public function testFileRemovedBeforeTheConfirmationIsPosted(): void
    {
        $file = self::FIXTURE_DIR . '/assets-zeta.txt';
        $this->withoutCustomRedirection()->asWebmaster();
        $values = $this->confirmationValues($file);
        unlink($this->path($file));

        $response = $this->request('POST', '/modules/system/admin.php', $values, $this->referer());

        self::assertStringContainsString('No file selected.', $this->message($response));
        self::assertFileExists($this->path(self::FIXTURE_DIR . '/assets-a&b.txt'));
    }

    /**
     * @scenario Deletion fails
     */
    public function testFileThatCannotBeRemovedIsLeftAsItIs(): void
    {
        $file = self::FIXTURE_DIR . '/assets-locked/assets-locked.txt';
        $this->createFile($file);
        $this->withoutCustomRedirection()->asWebmaster();
        $values = $this->confirmationValues($file);

        $windows = PHP_OS_FAMILY === 'Windows';
        if (!$windows && function_exists('posix_geteuid') && posix_geteuid() === 0) {
            self::markTestSkipped('A superuser can remove files from a read-only folder.');
        }

        // Windows refuses to delete a read-only file, other systems a file in a read-only folder
        $locked = $windows ? $this->path($file) : dirname($this->path($file));
        $before = fileperms($locked) & 0777;
        chmod($locked, $windows ? 0444 : 0555);

        try {
            $response = $this->request('POST', '/modules/system/admin.php', $values, $this->referer());
        } finally {
            chmod($locked, $before);
        }

        self::assertStringContainsString('The file could not be deleted.', $this->message($response));
        self::assertFileExists($this->path($file));
    }

    // -- Helpers -------------------------------------------------------------------

    private function asWebmaster(): static
    {
        $this->loginAsWebmaster();

        return $this;
    }

    private function withoutCustomRedirection(): static
    {
        TestSite::setConfig('use_custom_redirection', '0');

        return $this;
    }

    private function openPage(string $path): Crawler
    {
        return $this->browser->request('GET', static::url($path), [], [], $this->referer());
    }

    /**
     * @return array{Crawler, Response}
     */
    private function openConfirmation(string $file): array
    {
        $crawler = $this->openPage(self::PAGE . '&op=del&path=' . rawurlencode($file));

        return [$crawler, $this->browser->getInternalResponse()];
    }

    /**
     * What the confirmation form of a file posts: the fields and the token.
     *
     * @return array<string, mixed>
     */
    private function confirmationValues(string $file): array
    {
        [$crawler, $response] = $this->openConfirmation($file);
        self::assertSame(200, $response->getStatusCode(), self::serverLog());

        $values = $crawler->filterXPath('//div[@class="confirmMsg"]//form')->form()->getPhpValues();
        self::assertNotEmpty($values['XOOPS_TOKEN_REQUEST'] ?? null, 'The confirmation carries no token.');

        return $values;
    }

    /**
     * The text of a redirect: the page itself, or the page it leads to when the
     * site redirects with a Location header.
     */
    private function message(Response $response): string
    {
        if ($response->getStatusCode() === 302) {
            return $this->request('GET', (string) $response->getHeader('Location'))->getContent();
        }

        return $response->getContent();
    }

    private function deleteLinks(string $relative): string
    {
        $query = 'path=' . rawurlencode($relative);

        return sprintf('//a[contains(@href, "op=del") and substring(@href, string-length(@href) - %d) = "%s"]', strlen($query) - 1, $query);
    }

    /**
     * @return array<string, string>
     */
    private function referer(): array
    {
        return ['HTTP_REFERER' => static::url(self::PAGE)];
    }

    private function path(string $relative): string
    {
        return TestSite::legacyRoot() . '/uploads/' . $relative;
    }

    private function createFile(string $relative): void
    {
        $this->createFolder(dirname($relative));
        $path = $this->path($relative);
        file_put_contents($path, 'fixture');
        $this->created[] = $path;
    }

    private function createFolder(string $relative): void
    {
        $path = $this->path($relative);
        if (is_dir($path)) {
            return;
        }
        if (dirname($relative) !== '.') {
            $this->createFolder(dirname($relative));
        }
        mkdir($path);
        $this->created[] = $path;
    }

    private function grantAssetsRight(string $uname): void
    {
        $this->execute(
            "INSERT INTO %group_permission% (gperm_groupid, gperm_itemid, gperm_modid, gperm_name) "
            . "SELECT l.groupid, 27, (SELECT mid FROM %modules% WHERE dirname = 'system'), 'system_admin' "
            . "FROM %groups_users_link% l JOIN %users% u ON u.uid = l.uid WHERE u.uname = '" . $uname . "' "
            . "AND l.groupid NOT IN (1, 2, 3)"
        );
    }

    private function execute(string $sql): void
    {
        TestSite::connection()->exec($this->prefixed($sql));
    }

    private function query(string $sql): string
    {
        return (string) TestSite::connection()->query($this->prefixed($sql))->fetchColumn();
    }

    private function prefixed(string $sql): string
    {
        return (string) preg_replace_callback('/%(\w+)%/', static fn (array $m): string => TestSite::table($m[1]), $sql);
    }
}
