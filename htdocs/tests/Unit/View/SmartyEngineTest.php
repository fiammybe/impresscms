<?php
/**
 * Smoke test for the Composer-installed Smarty 5 engine and the ImpressCMS
 * additions in Icms\View\Smarty (legacy syntax pre-filter, tag registration).
 *
 * Renders with the production delimiters and E_ALL, so PHP notices raised by
 * the engine fail the test.
 *
 * @package icms\tests
 */

declare(strict_types=1);

namespace Icms\Tests\Unit\View;

use Icms\View\Smarty\LegacySyntaxFilter;
use Icms\View\Smarty\Setup;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Smarty\Smarty;

final class SmartyEngineTest extends TestCase
{
    private string $base;

    protected function setUp(): void
    {
        $this->base = sys_get_temp_dir() . '/icms-smarty-' . bin2hex(random_bytes(4));

        mkdir("{$this->base}/compile", 0777, true);
        mkdir("{$this->base}/templates", 0777, true);
    }

    protected function tearDown(): void
    {
        foreach (['compile', 'templates'] as $dir) {
            foreach (glob("{$this->base}/$dir/*") ?: [] as $file) {
                unlink($file);
            }
            rmdir("{$this->base}/$dir");
        }
        rmdir($this->base);
    }

    private function engine(): Smarty
    {
        $smarty = new Smarty();
        $smarty->setLeftDelimiter('<{');
        $smarty->setRightDelimiter('}>');
        $smarty->setCompileDir("{$this->base}/compile");
        $smarty->setTemplateDir("{$this->base}/templates");
        $smarty->muteUndefinedOrNullWarnings();

        Setup::apply($smarty, false);

        return $smarty;
    }

    private function write(string $name, string $source): void
    {
        file_put_contents("{$this->base}/templates/$name", $source);
    }

    private function render(Smarty $smarty, string $name, string $source): string
    {
        $this->write($name, $source);

        return $smarty->fetch($name);
    }

    /**
     * @return list<string> the deprecation messages raised while running $callback
     */
    private function deprecationsFrom(callable $callback): array
    {
        $messages = [];
        set_error_handler(static function (int $level, string $message) use (&$messages): bool {
            $messages[] = $message;

            return true;
        }, E_USER_DEPRECATED);

        try {
            $callback();
        } finally {
            restore_error_handler();
        }

        return $messages;
    }

    public function testSmartyFiveComesFromComposerPackage(): void
    {
        $file = str_replace('\\', '/', (string) (new \ReflectionClass(Smarty::class))->getFileName());

        $this->assertStringContainsString('/vendor/smarty/smarty/', $file);
        $this->assertSame(5, (int) explode('.', Smarty::SMARTY_VERSION)[0]);
    }

    public function testRendersVariablesWithCustomDelimiters(): void
    {
        $smarty = $this->engine();
        $smarty->assign('name', 'ImpressCMS');

        $this->assertSame('Hello ImpressCMS', $this->render($smarty, 'hello.html', 'Hello <{$name}>'));
    }

    public function testUndefinedVariablesRenderEmptyLikeSmartyTwo(): void
    {
        $this->assertSame('[]', $this->render($this->engine(), 'undef.html', '[<{$missing}>]'));
    }

    public function testSectionSubstrAndArrayCountModifiersStillWork(): void
    {
        $smarty = $this->engine();
        $smarty->assign('rows', ['abcdef', 'ghijkl']);

        $out = $this->render(
            $smarty,
            'stock.html',
            '<{section name=i loop=$rows}><{$rows[i]|substr:0:2|escape}><{/section}>/<{$rows|@count}>'
        );

        $this->assertSame('abgh/2', $out);
    }

    public function testSpaceAfterTheDelimiterStillOpensATag(): void
    {
        $smarty = $this->engine();
        $smarty->assign('on', true);

        $this->assertSame('yes', $this->render($smarty, 'space.html', '<{ if $on }>yes<{ /if }>'));
    }

    public function testIncludeqIsAnAliasOfIncludeAndIsDeprecated(): void
    {
        $smarty = $this->engine();
        $this->write('child.html', '[<{$label}>]');
        $out = '';

        $deprecations = $this->deprecationsFrom(function () use ($smarty, &$out): void {
            $out = $this->render($smarty, 'parent.html', '<{includeq file="child.html" label="x"}>');
        });

        $this->assertSame('[x]', $out);
        $this->assertCount(1, $deprecations);
        $this->assertStringContainsString('includeq', $deprecations[0]);
    }

    public function testForeachqIsAnAliasOfForeachAndIsDeprecated(): void
    {
        $smarty = $this->engine();
        $smarty->assign('items', ['a' => 1, 'b' => 2]);
        $out = '';

        $deprecations = $this->deprecationsFrom(function () use ($smarty, &$out): void {
            $out = $this->render(
                $smarty,
                'loop.html',
                '<{foreachq from=$items key=k item=v}><{$k}>=<{$v}>;<{/foreach}>'
            );
        });

        $this->assertSame('a=1;b=2;', $out);
        $this->assertCount(1, $deprecations);
    }

    public function testModernSyntaxRaisesNoDeprecation(): void
    {
        $smarty = $this->engine();
        $this->write('child.html', 'x');

        $deprecations = $this->deprecationsFrom(function () use ($smarty): void {
            $this->render($smarty, 'modern.html', '<{include file="child.html"}><{foreach from=[1] item=i}><{$i}><{/foreach}>');
        });

        $this->assertSame([], $deprecations);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function legacyTagProvider(): array
    {
        return [
            'xoImgUrl path' => ['<{xoImgUrl icons/favicon.ico}>', '<{xoImgUrl path="icons/favicon.ico"}>'],
            'xoImgUrl in attribute' => ['href="<{xoImgUrl  img/logo.png }>"', 'href="<{xoImgUrl path="img/logo.png"}>"'],
            'xoAppUrl root' => ['<{xoAppUrl /}>', '<{xoAppUrl url="/"}>'],
            'xoAppUrl with parameters' => ['<{xoAppUrl user.php op=login}>', '<{xoAppUrl url="user.php" op=login}>'],
            'xoAppUrl variable' => ['<{xoAppUrl $target}>', '<{xoAppUrl url=$target}>'],
            'two tags on one line' => [
                '<a href="<{xoAppUrl /}>" title=""><img src="<{xoImgUrl img/logo.png}>" alt="<{$name}>" /></a>',
                '<a href="<{xoAppUrl url="/"}>" title=""><img src="<{xoImgUrl path="img/logo.png"}>" alt="<{$name}>" /></a>',
            ],
            'cycle unquoted values' => ['<{cycle values=odd,even assign=class}>', '<{cycle values="odd,even" assign=class}>'],
            'cycle quoted values' => ['<{cycle values="odd,even"}>', '<{cycle values="odd,even"}>'],
            'xoImgUrl already modern' => ['<{xoImgUrl path="a.png"}>', '<{xoImgUrl path="a.png"}>'],
            'xoAppUrl already modern' => ['<{xoAppUrl url="a.php" b=1}>', '<{xoAppUrl url="a.php" b=1}>'],
        ];
    }

    #[DataProvider('legacyTagProvider')]
    public function testLegacyTagsAreRewrittenToAttributeSyntax(string $legacy, string $expected): void
    {
        $smarty = $this->engine();
        $template = $smarty->createTemplate('eval:x');
        $result = '';

        $this->deprecationsFrom(function () use ($legacy, $template, &$result): void {
            $result = (new LegacySyntaxFilter())($legacy, $template);
        });

        $this->assertSame($expected, $result);
    }

    public function testImpressCmsTagsAreRegistered(): void
    {
        $smarty = $this->engine();

        foreach (['xoAppUrl', 'xoImgUrl', 'xoInboxCount', 'xoPageNav', 'xoops_link', 'resized_image'] as $tag) {
            $this->assertNotNull($smarty->getRegisteredPlugin('function', $tag), $tag);
        }
    }
}
