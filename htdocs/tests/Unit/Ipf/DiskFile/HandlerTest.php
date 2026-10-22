<?php
/**
 * Icms\Ipf\DiskFile\Handler: listing, loading and filtering the files of a
 * folder, and refusing paths outside its root.
 *
 * @package icms\tests
 */

declare(strict_types=1);

namespace Icms\Tests\Unit\Ipf\DiskFile;

use Icms\Db\Criteria\Compo;
use Icms\Db\Criteria\Item;
use Icms\Ipf\DiskFile\Entity;
use Icms\Ipf\DiskFile\Exception;
use Icms\Ipf\DiskFile\Handler;
use Icms\Ipf\DiskFile\ImageEntity;
use Icms\Ipf\DiskFile\VideoEntity;
use Icms\Ipf\DiskFile\ZipEntity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('spec:ipf-disk-files')]
final class HandlerTest extends TestCase
{
    private string $dir;
    private string $root;
    private Handler $handler;

    protected function setUp(): void
    {
        $this->dir = str_replace('\\', '/', sys_get_temp_dir()) . '/icms-diskfile-' . bin2hex(random_bytes(4));
        $this->root = $this->dir . '/root';
        mkdir($this->root . '/sub', 0777, true);

        $this->put('b.txt', 'bb', 1_000);
        $this->put('a.TXT', 'a', 3_000);
        $this->put('c.zip', 'not really a zip', 2_000);
        $this->put('d.png', 'x', 4_000);
        $this->put('e.mp4', 'x', 5_000);
        $this->put('file10.log', 'x', 6_000);
        $this->put('file9.log', 'x', 7_000);
        $this->put('sub/nested.txt', 'x', 8_000);
        file_put_contents($this->dir . '/outside.txt', 'outside');

        $this->handler = new Handler($this->root);
    }

    protected function tearDown(): void
    {
        foreach (new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        ) as $item) {
            $item->isDir() && !$item->isLink() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($this->dir);
    }

    /**
     * @scenario List a folder
     */
    public function testListsTheFilesOfAFolderByBaseName(): void
    {
        $list = $this->handler->getList($this->root);

        self::assertSame(
            ['a.TXT', 'b.txt', 'c.zip', 'd.png', 'e.mp4', 'file9.log', 'file10.log'],
            array_values($list),
            'natural, case-insensitive order; sub folders are skipped',
        );
        self::assertSame($this->root . '/b.txt', array_search('b.txt', $list, true));
    }

    public function testSortsByAnyFieldInBothDirections(): void
    {
        self::assertSame(
            ['file9.log', 'file10.log', 'e.mp4', 'd.png', 'a.TXT', 'c.zip', 'b.txt'],
            array_values($this->handler->getList($this->root, null, 'mtime', 'DESC')),
        );
        self::assertSame(
            'a.TXT',
            array_values($this->handler->getList($this->root, null, 'unknown field', 'sideways'))[0],
            'an unknown sort field falls back to the base name, an unknown order to ASC',
        );
    }

    /**
     * @scenario Filter with criteria
     */
    public function testFiltersWithCriteria(): void
    {
        self::assertSame(['a.TXT', 'b.txt'], $this->names(new Item('extension', 'TXT')));
        self::assertSame(['e.mp4', 'file9.log', 'file10.log'], $this->names(new Item('mtime', '4001', '>=')));
        self::assertSame(['a.TXT', 'b.txt'], $this->names(new Item('basename', '_.txt', 'LIKE')));
        self::assertSame(['c.zip'], $this->names(new Item('extension', "('zip','jpg')", 'IN')));
        self::assertNotContains('c.zip', $this->names(new Item('extension', 'zip', '!=')));
        self::assertSame(['file9.log', 'file10.log'], $this->names(new Item('basename', 'file%', 'LIKE')));
        self::assertSame(
            ['b.txt'],
            $this->names(new Item('size', '2', '=')),
        );
    }

    public function testCombinesCriteriaWithAndOr(): void
    {
        $and = new Compo(new Item('extension', 'txt'));
        $and->add(new Item('size', '1', '>'));
        self::assertSame(['b.txt'], $this->names($and));

        $or = new Compo(new Item('extension', 'png'));
        $or->add(new Item('extension', 'zip'), 'OR');
        self::assertSame(['c.zip', 'd.png'], $this->names($or));
    }

    public function testAppliesStartAndLimit(): void
    {
        $criteria = new Compo(new Item('size', '0', '>'));
        $criteria->setStart(1);
        $criteria->setLimit(2);

        self::assertSame(['b.txt', 'c.zip'], $this->names($criteria));
    }

    /**
     * @scenario Unsupported criteria
     */
    #[DataProvider('unsupportedCriteria')]
    public function testRefusesCriteriaItCannotEvaluate(Item $criteria): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->handler->getObjects($this->root, $criteria);
    }

    /**
     * @return array<string, array{0: Item}>
     */
    public static function unsupportedCriteria(): array
    {
        return [
            'field' => [new Item('mime_type', 'image/png')],
            'operator' => [new Item('size', '1', 'BETWEEN')],
        ];
    }

    /**
     * @scenario Typed file objects
     */
    public function testLoadsTypedObjectsWithTheFileProperties(): void
    {
        $file = $this->handler->get($this->root . '/b.txt');

        self::assertSame(Entity::class, $file::class);
        self::assertSame($this->root . '/b.txt', $file->path());
        self::assertSame($this->root, $file->getVar('dirname', 'n'));
        self::assertSame('b.txt', $file->getVar('basename', 'n'));
        self::assertSame('txt', $file->getVar('extension', 'n'));
        self::assertSame(2, $file->getVar('size', 'n'));
        self::assertSame(1_000, $file->getVar('mtime', 'n'));
        self::assertSame(1, $file->getVar('is_readable', 'n'));
        self::assertFalse($file->isNew());
        self::assertFalse($file->isDirty());
        self::assertSame('text/plain', $file->mimeType());

        self::assertInstanceOf(ZipEntity::class, $this->handler->get($this->root . '/c.zip'));
        self::assertInstanceOf(ImageEntity::class, $this->handler->get($this->root . '/d.png'));
        self::assertInstanceOf(VideoEntity::class, $this->handler->get($this->root . '/e.mp4'));
        self::assertInstanceOf(Entity::class, $this->handler->get($this->root . '/a.TXT'));
    }

    public function testExtraBehaviourOfTheTypedObjects(): void
    {
        $zip = new \ZipArchive();
        $zip->open($this->root . '/real.zip', \ZipArchive::CREATE);
        $zip->addFromString('one', '1');
        $zip->addFromString('two', '2');
        $zip->close();

        $png = imagecreatetruecolor(3, 2);
        imagepng($png, $this->root . '/real.png');

        $realZip = $this->handler->get($this->root . '/real.zip');
        $realPng = $this->handler->get($this->root . '/real.png');
        $fakeZip = $this->handler->get($this->root . '/c.zip');
        $fakePng = $this->handler->get($this->root . '/d.png');

        self::assertInstanceOf(ZipEntity::class, $realZip);
        self::assertInstanceOf(ImageEntity::class, $realPng);
        self::assertInstanceOf(ZipEntity::class, $fakeZip);
        self::assertInstanceOf(ImageEntity::class, $fakePng);
        self::assertSame(2, $realZip->getEntryCount());
        self::assertSame(['width' => 3, 'height' => 2], $realPng->getDimensions());
        self::assertNull($fakeZip->getEntryCount());
        self::assertNull($fakePng->getDimensions());
    }

    /**
     * @scenario Paths outside the root
     */
    public function testRefusesPathsOutsideTheRoot(): void
    {
        foreach ([$this->dir . '/outside.txt', $this->root . '/../outside.txt', $this->root . '/missing.txt', $this->root . '/sub', ''] as $path) {
            try {
                $this->handler->get($path);
                self::fail("get('{$path}') should be refused");
            } catch (Exception) {
                $this->addToAssertionCount(1);
            }
        }

        $this->expectException(Exception::class);
        $this->handler->getObjects($this->dir);
    }

    /**
     * @scenario Paths outside the root
     */
    public function testRefusesLinksLeavingTheRoot(): void
    {
        if (!@symlink($this->dir . '/outside.txt', $this->root . '/link.txt')) {
            self::markTestSkipped('Symbolic links are not available.');
        }

        self::assertNotContains('link.txt', $this->handler->getList($this->root));

        $this->expectException(Exception::class);
        $this->handler->get($this->root . '/link.txt');
    }

    /**
     * @scenario Folder tree
     */
    public function testListsSubFoldersWithoutFollowingLinks(): void
    {
        mkdir($this->root . '/Sub10');
        mkdir($this->root . '/sub9');

        self::assertSame(
            [$this->root . '/sub' => 'sub', $this->root . '/sub9' => 'sub9', $this->root . '/Sub10' => 'Sub10'],
            $this->handler->getFolders($this->root),
        );

        if (@symlink($this->dir, $this->root . '/link')) {
            self::assertArrayNotHasKey($this->root . '/link', $this->handler->getFolders($this->root));
        }

        $this->expectException(Exception::class);
        $this->handler->getFolders($this->dir);
    }

    public function testNeedsAnExistingRoot(): void
    {
        $this->expectException(Exception::class);

        new Handler($this->dir . '/missing');
    }

    /**
     * @return list<string>
     */
    private function names(\Icms\Db\Criteria\Element $criteria): array
    {
        return array_values($this->handler->getList($this->root, $criteria));
    }

    private function put(string $name, string $content, int $mtime): void
    {
        $path = $this->root . '/' . $name;
        file_put_contents($path, $content);
        touch($path, $mtime);
    }
}
