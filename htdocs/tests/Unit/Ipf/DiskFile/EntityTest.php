<?php
/**
 * Icms\Ipf\DiskFile\Entity: the actions on a file, none of which may overwrite
 * a file or leave the handler's root.
 *
 * @package icms\tests
 */

declare(strict_types=1);

namespace Icms\Tests\Unit\Ipf\DiskFile;

use Icms\Ipf\DiskFile\Exception;
use Icms\Ipf\DiskFile\Handler;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('spec:ipf-disk-files')]
final class EntityTest extends TestCase
{
    private string $dir;
    private string $root;
    private Handler $handler;

    protected function setUp(): void
    {
        $this->dir = str_replace('\\', '/', sys_get_temp_dir()) . '/icms-diskfile-' . bin2hex(random_bytes(4));
        $this->root = $this->dir . '/root';
        mkdir($this->root . '/in', 0777, true);
        mkdir($this->dir . '/elsewhere');

        file_put_contents($this->root . '/one.txt', '1');
        file_put_contents($this->root . '/two.txt', '2');

        $this->handler = new Handler($this->root);
    }

    protected function tearDown(): void
    {
        foreach (new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        ) as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($this->dir);
    }

    /**
     * @scenario Rename a file
     */
    public function testRenamesTheFileAndReloadsTheObject(): void
    {
        $file = $this->handler->get($this->root . '/one.txt');

        $file->rename('uno.md');

        self::assertFileDoesNotExist($this->root . '/one.txt');
        self::assertFileExists($this->root . '/uno.md');
        self::assertSame($this->root . '/uno.md', $file->path());
        self::assertSame('md', $file->getVar('extension', 'n'));
    }

    /**
     * @scenario Rename a file
     */
    #[DataProvider('badNames')]
    public function testRefusesNamesThatAreNotFileNames(string $name): void
    {
        $file = $this->handler->get($this->root . '/one.txt');

        try {
            $file->rename($name);
            self::fail('should be refused');
        } catch (Exception) {
            self::assertFileExists($this->root . '/one.txt');
        }
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function badNames(): array
    {
        return [
            'empty' => [''],
            'dot' => ['.'],
            'dot dot' => ['..'],
            'slash' => ['../escape.txt'],
            'backslash' => ['..\\escape.txt'],
            'sub folder' => ['in/x.txt'],
            'nul' => ["x\0.txt"],
        ];
    }

    /**
     * @scenario Actions never overwrite
     */
    public function testRenameWillNotOverwrite(): void
    {
        $this->expectException(Exception::class);

        try {
            $this->handler->get($this->root . '/one.txt')->rename('two.txt');
        } finally {
            self::assertSame('2', file_get_contents($this->root . '/two.txt'));
            self::assertFileExists($this->root . '/one.txt');
        }
    }

    /**
     * @scenario Move a file
     */
    public function testMovesToAnotherFolderInTheRoot(): void
    {
        $file = $this->handler->get($this->root . '/one.txt');

        $file->moveTo($this->root . '/in');

        self::assertFileDoesNotExist($this->root . '/one.txt');
        self::assertSame($this->root . '/in/one.txt', $file->path());
        self::assertSame($this->root . '/in', $file->getVar('dirname', 'n'));
    }

    /**
     * @scenario Copy a file
     */
    public function testCopiesAndReturnsTheCopy(): void
    {
        $file = $this->handler->get($this->root . '/one.txt');

        $copy = $file->copyTo($this->root . '/in');

        self::assertSame($this->root . '/in/one.txt', $copy->path());
        self::assertFileExists($this->root . '/one.txt');
        self::assertSame('1', file_get_contents($copy->path()));
        self::assertSame($this->root . '/one.txt', $file->path(), 'the original object is unchanged');
    }

    /**
     * @scenario Actions never overwrite
     */
    public function testMoveAndCopyWillNotOverwrite(): void
    {
        file_put_contents($this->root . '/in/one.txt', 'existing');
        $file = $this->handler->get($this->root . '/one.txt');

        foreach (['moveTo', 'copyTo'] as $action) {
            try {
                $file->$action($this->root . '/in');
                self::fail("{$action} should be refused");
            } catch (Exception) {
                self::assertSame('existing', file_get_contents($this->root . '/in/one.txt'));
            }
        }
    }

    /**
     * @scenario Actions stay inside the root
     */
    public function testMoveAndCopyStayInsideTheRoot(): void
    {
        $file = $this->handler->get($this->root . '/one.txt');

        foreach (['moveTo', 'copyTo'] as $action) {
            foreach ([$this->dir . '/elsewhere', $this->root . '/../elsewhere', $this->root . '/missing'] as $target) {
                try {
                    $file->$action($target);
                    self::fail("{$action}('{$target}') should be refused");
                } catch (Exception) {
                    $this->addToAssertionCount(1);
                }
            }
        }

        self::assertFileExists($this->root . '/one.txt');
        self::assertSame([], glob($this->dir . '/elsewhere/*'));
    }

    /**
     * @scenario Delete a file
     */
    public function testDeletesTheFile(): void
    {
        $file = $this->handler->get($this->root . '/one.txt');

        $file->delete();

        self::assertFileDoesNotExist($this->root . '/one.txt');
        self::assertTrue($file->isNew());

        $this->expectException(Exception::class);
        $file->delete();
    }

    public function testIsNoTableBackedEntity(): void
    {
        // Not an Icms\Ipf\Entity: it has no table, so no store(), and delete() takes no $force.
        self::assertNotInstanceOf(\Icms\Ipf\Entity::class, $this->handler->get($this->root . '/one.txt'));
        self::assertFalse(method_exists($this->handler->get($this->root . '/one.txt'), 'store'));
    }
}
