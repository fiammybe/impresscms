<?php
/**
 * bin/find-hardcoded-html.php: the inventory scanner and the CI guard.
 *
 * @package icms\tests
 */

declare(strict_types=1);

namespace Icms\Tests\Unit\View;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('spec:library-templating')]
final class HardcodedHtmlGuardTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/icms-html-guard-' . bin2hex(random_bytes(4));
        mkdir($this->dir . '/lib', 0777, true);
        file_put_contents($this->dir . '/lib/Widget.php', "<?php\n/** Renders a <b>widget</b> */\nclass Widget {\n\tfunction show() {\n\t\t// <i>note</i>\n\t\techo '<div class=\"widget\">x</div>';\n\t}\n}\n");
        file_put_contents($this->dir . '/baseline.txt', "# file<TAB>count\n");
    }

    protected function tearDown(): void
    {
        foreach (['/lib/Widget.php', '/baseline.txt'] as $file) {
            @unlink($this->dir . $file);
        }
        @rmdir($this->dir . '/lib');
        @rmdir($this->dir);
    }

    /**
     * @scenario Running the scanner
     */
    public function testScannerListsMarkupButNotComments(): void
    {
        [$output, $exit] = $this->scan([$this->dir . '/lib']);

        self::assertSame(0, $exit);
        self::assertStringContainsString('Widget.php:6', $output);
        self::assertStringContainsString('Widget::show', $output);
        self::assertStringContainsString('<div class="widget">x</div>', $output);
        self::assertStringNotContainsString('<b>widget</b>', $output);
        self::assertStringNotContainsString('<i>note</i>', $output);
    }

    /**
     * @scenario New hardcoded markup
     */
    public function testGuardFailsOnMarkupOutsideTheBaseline(): void
    {
        [$output, $exit] = $this->scan(['--check=' . $this->dir . '/baseline.txt', $this->dir . '/lib']);

        self::assertSame(1, $exit);
        self::assertStringContainsString('NEW hardcoded HTML', $output);
    }

    public function testLibraryIsWithinItsBaseline(): void
    {
        $root = dirname(__DIR__, 4);
        [$output, $exit] = $this->scan(['--check=' . $root . '/bin/hardcoded-html-baseline.txt']);

        self::assertSame(0, $exit, $output);
    }

    /**
     * @param list<string> $arguments
     *
     * @return array{string, int}
     */
    private function scan(array $arguments): array
    {
        $script = dirname(__DIR__, 4) . '/bin/find-hardcoded-html.php';
        $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($script) . ' ' . implode(' ', array_map('escapeshellarg', $arguments)) . ' 2>&1';
        exec($command, $lines, $exit);

        return [implode("\n", $lines), $exit];
    }
}
