<?php
/**
 * Icms\Ipf\DiskFile\FolderTree and TreeView: a folder tree as the rows of the
 * IPF tree table template.
 *
 * @package icms\tests
 */

declare(strict_types=1);

namespace Icms\Tests\Unit\Ipf\DiskFile;

use Icms\Db\Criteria\Item;
use Icms\Ipf\DiskFile\Exception;
use Icms\Ipf\DiskFile\FolderTree;
use Icms\Ipf\DiskFile\Handler;
use Icms\Ipf\DiskFile\TreeNode;
use Icms\Ipf\DiskFile\TreeView;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('spec:ipf-disk-files')]
final class TreeTest extends TestCase
{
    private string $dir;
    private string $root;
    private Handler $handler;

    protected function setUp(): void
    {
        $this->dir = str_replace('\\', '/', sys_get_temp_dir()) . '/icms-difftree-' . bin2hex(random_bytes(4));
        $this->root = $this->dir . '/root';

        foreach (['b', 'a/deep/deeper', 'a/empty', 'c'] as $folder) {
            mkdir($this->root . '/' . $folder, 0777, true);
        }
        mkdir($this->dir . '/outside');

        $this->put('top.txt', 'tt', 1_000);
        $this->put('a/one.txt', '1', 2_000);
        $this->put('a/deep/two.log', '22', 3_000);
        $this->put('a/deep/deeper/three.txt', '333', 4_000);
        $this->put("b/a&b'c.txt", 'x', 5_000);
        file_put_contents($this->dir . '/outside/out.txt', 'out');

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
     * @scenario Folder tree
     */
    public function testListsSubFoldersBeforeFilesDepthFirst(): void
    {
        $nodes = (new FolderTree($this->handler))->nodes($this->root);

        self::assertSame(
            [
                '0 a/', '1 deep/', '2 deeper/', '3 three.txt', '2 two.log', '1 empty/', '1 one.txt',
                "0 b/", "1 a&b'c.txt", '0 c/', '0 top.txt',
            ],
            $this->describe($nodes),
        );

        $byName = array_column(array_map(static fn (TreeNode $n): array => [$n->name, $n], $nodes), 1, 0);
        self::assertNull($byName['a']->parentPath);
        self::assertSame($this->root . '/a', $byName['deep']->parentPath);
        self::assertSame($this->root . '/a/deep/deeper', $byName['three.txt']->parentPath);
        self::assertTrue($byName['a']->isFolder());
        self::assertSame($this->root . '/a/deep/two.log', $byName['two.log']->file?->path());
    }

    /**
     * @scenario Folder tree
     */
    public function testCriteriaLeaveOutFoldersWithoutMatches(): void
    {
        $nodes = (new FolderTree($this->handler))->nodes($this->root, new Item('extension', 'log'));

        self::assertSame(['0 a/', '1 deep/', '2 two.log'], $this->describe($nodes));
    }

    /**
     * @scenario Folder tree
     */
    public function testSortsTheFilesOfEveryFolder(): void
    {
        $this->put('a/zz.txt', 'x', 9_000);

        $nodes = (new FolderTree($this->handler))->nodes($this->root, null, 'mtime', 'DESC');

        $inA = array_values(array_filter($this->describe($nodes), static fn (string $d): bool => str_starts_with($d, '1 ') && str_ends_with($d, '.txt')));
        self::assertSame(['1 zz.txt', '1 one.txt', "1 a&b'c.txt"], $inA, 'files of a come newest first; the folder b has its own');
    }

    /**
     * @scenario Folder tree
     */
    public function testStopsAtTheMaximumDepth(): void
    {
        $nodes = (new FolderTree($this->handler, 2))->nodes($this->root);

        self::assertNotContains('2 deeper/', $this->describe($nodes));
        self::assertNotContains('3 three.txt', $this->describe($nodes));
        self::assertContains('2 two.log', $this->describe($nodes));

        $this->expectException(\InvalidArgumentException::class);
        new FolderTree($this->handler, 0);
    }

    /**
     * @scenario Folder tree stays inside the root
     */
    public function testRefusesAFolderOutsideTheRoot(): void
    {
        $this->expectException(Exception::class);

        (new FolderTree($this->handler))->nodes($this->dir . '/outside');
    }

    /**
     * @scenario Folder tree stays inside the root
     */
    public function testDoesNotFollowLinkedFolders(): void
    {
        if (!@symlink($this->dir . '/outside', $this->root . '/link') || !@symlink($this->root, $this->root . '/a/loop')) {
            self::markTestSkipped('Symbolic links are not available.');
        }

        $names = $this->describe((new FolderTree($this->handler))->nodes($this->root));

        self::assertNotContains('0 link/', $names);
        self::assertNotContains('1 loop/', $names);
        self::assertNotContains('1 out.txt', $names);
        self::assertCount(11, $names);
    }

    /**
     * @scenario Rows for the tree table template
     */
    public function testTemplateVariablesHaveTheShapeOfTheTreeTable(): void
    {
        $vars = $this->view()->templateVariables();

        self::assertTrue($vars['icms_isTree']);
        self::assertFalse($vars['icms_showFilterAndLimit']);
        self::assertSame('icms_folder_tree', $vars['icms_id']);
        self::assertSame(
            [
                ['key' => 'basename', 'caption' => 'Name', 'width' => '', 'align' => 'left'],
                ['key' => 'size', 'caption' => 'Size', 'width' => '', 'align' => 'right'],
                ['key' => 'mtime', 'caption' => 'Modified', 'width' => '', 'align' => 'left'],
            ],
            $vars['icms_columns'],
        );
        self::assertSame(4, $vars['icms_colspan']);
        self::assertFalse($vars['icms_has_actions']);

        $rows = $vars['icms_persistable_objects'];
        self::assertCount(11, $rows);
        self::assertSame($this->root . '/a', $rows[0]['id']);
        self::assertSame(['odd' => 'even', 'even' => 'odd'][$rows[0]['class']], $rows[1]['class'], 'rows alternate');

        $top = $rows[10];
        self::assertSame($this->root . '/top.txt', $top['id']);
        self::assertSame(
            [
                ['keyname' => 'basename', 'value' => 'top.txt', 'class' => 'head', 'width' => '', 'align' => 'left'],
                ['keyname' => 'size', 'value' => '2', 'class' => 'odd', 'width' => '', 'align' => 'right'],
                ['keyname' => 'mtime', 'value' => '1970-01-01 00:16', 'class' => 'even', 'width' => '', 'align' => 'left'],
            ],
            $top['columns'],
        );
        self::assertSame([], $top['actions']);
    }

    /**
     * @scenario Rows for the tree table template
     */
    public function testIndentsByLevelAndMarksFolders(): void
    {
        $rows = $this->view()->templateVariables()['icms_persistable_objects'];
        $first = static fn (array $row): string => $row['columns'][0]['value'];

        self::assertSame('<strong>a/</strong>', $first($rows[0]));
        self::assertSame('--&nbsp;<strong>deep/</strong>', $first($rows[1]));
        self::assertSame('----&nbsp;<strong>deeper/</strong>', $first($rows[2]));
        self::assertSame('------&nbsp;three.txt', $first($rows[3]));
        self::assertSame(['', ''], [$rows[0]['columns'][1]['value'], $rows[0]['columns'][2]['value']], 'a folder has no size or date');
    }

    /**
     * @scenario Rows for the tree table template
     */
    public function testEscapesNamesFromTheDisk(): void
    {
        $rows = $this->view()->templateVariables()['icms_persistable_objects'];

        self::assertSame('--&nbsp;a&amp;b&#039;c.txt', $rows[8]['columns'][0]['value']);

        if (@file_put_contents($this->root . '/c/<i>.txt', 'x') === false) {
            self::markTestSkipped('This file system does not allow "<" in file names.');
        }

        $rows = $this->view()->templateVariables()['icms_persistable_objects'];
        self::assertSame('--&nbsp;&lt;i&gt;.txt', $rows[10]['columns'][0]['value']);
        self::assertSame($this->root . '/c/&lt;i&gt;.txt', $rows[10]['id'], 'the id ends up inside an attribute');
        self::assertStringNotContainsString('<i>', (string) json_encode($rows));
    }

    /**
     * @scenario Rows for the tree table template
     */
    public function testActionsAreBuiltPerNode(): void
    {
        $view = $this->view()->addAction(
            static fn (TreeNode $node): string => $node->isFolder() ? '' : '<a href="?del=' . rawurlencode($node->path) . '">x</a>',
        );

        $vars = $view->templateVariables(new Item('extension', 'log'));

        self::assertTrue($vars['icms_has_actions']);
        self::assertSame(30, $vars['icms_actions_column_width']);
        self::assertSame([], $vars['icms_persistable_objects'][0]['actions'], 'the folder has none');
        self::assertCount(1, $vars['icms_persistable_objects'][2]['actions']);
        self::assertStringContainsString('two.log', $vars['icms_persistable_objects'][2]['actions'][0]);
    }

    public function testRefusesColumnsItDoesNotKnow(): void
    {
        foreach ([[], ['mime_type' => 'Type']] as $columns) {
            try {
                new TreeView(new FolderTree($this->handler), $this->root, $columns);
                self::fail('should be refused');
            } catch (\InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    private function view(): TreeView
    {
        return new TreeView(new FolderTree($this->handler), $this->root);
    }

    /**
     * @param list<TreeNode> $nodes
     *
     * @return list<string>
     */
    private function describe(array $nodes): array
    {
        return array_map(
            static fn (TreeNode $node): string => $node->level . ' ' . $node->name . ($node->isFolder() ? '/' : ''),
            $nodes,
        );
    }

    private function put(string $name, string $content, int $mtime): void
    {
        $path = $this->root . '/' . $name;
        file_put_contents($path, $content);
        touch($path, $mtime);
    }
}
