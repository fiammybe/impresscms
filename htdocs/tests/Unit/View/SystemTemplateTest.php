<?php
/**
 * Icms\View\SystemTemplate and the library templates of the system module.
 *
 * @package icms\tests
 */

declare(strict_types=1);

namespace Icms\Tests\Unit\View;

use Icms\Tests\Support\TemplateEnvironment;
use Icms\View\SystemTemplate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('spec:library-templating')]
final class SystemTemplateTest extends TestCase
{
    protected function setUp(): void
    {
        TemplateEnvironment::boot();
    }

    protected function tearDown(): void
    {
        SystemTemplate::setEngineFactory(null);
    }

    public function testImportedTemplatesAreReadThroughTheDatabaseResource(): void
    {
        self::assertSame('db:icms_module_mainlink.html', SystemTemplate::resource('icms_module_mainlink.html'));
        self::assertSame('<a href="u">n</a>', SystemTemplate::fetch('icms_module_mainlink.html', ['url' => 'u', 'name' => 'n']));
    }

    /**
     * @scenario Template not yet imported
     */
    public function testTemplateFileIsUsedUntilTheSystemModuleIsUpdated(): void
    {
        // a template set without the new templates: db: finds nothing
        SystemTemplate::setEngineFactory(static function (): \Smarty {
            $smarty = new \Smarty();
            $smarty->left_delimiter = '<{';
            $smarty->right_delimiter = '}>';
            $smarty->compile_dir = ICMS_COMPILE_PATH;
            $smarty->register_resource('db', [
                static fn (): bool => false,
                static fn (): bool => false,
                static fn (): bool => true,
                static fn (): bool => true,
            ]);

            return $smarty;
        });

        self::assertSame('file:' . ICMS_ROOT_PATH . '/modules/system/templates/icms_form_elements_hidden_display.html', SystemTemplate::resource('icms_form_elements_hidden_display.html'));
        $hidden = new \Icms\Form\Elements\Hidden('XOOPS_TOKEN_REQUEST', 'abc');
        self::assertSame("<input type='hidden' name='XOOPS_TOKEN_REQUEST' id='XOOPS_TOKEN_REQUEST' value='abc' />", $hidden->render(), 'tokens still render');
    }

    public function testResourceNamesAreKept(): void
    {
        self::assertSame('file:/x/y.html', SystemTemplate::resource('file:/x/y.html'));
        self::assertSame('db:mymodule_form.html', SystemTemplate::resource('db:mymodule_form.html'));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function libraryTemplates(): iterable
    {
        foreach (glob(dirname(__DIR__, 3) . '/modules/system/templates/icms_*.html') ?: [] as $file) {
            yield basename($file) => [$file];
        }
    }

    #[DataProvider('libraryTemplates')]
    public function testTemplateIsRegistered(string $file): void
    {
        $version = (string) file_get_contents(dirname(__DIR__, 3) . '/modules/system/icms_version.php');
        self::assertStringContainsString('"' . basename($file) . '"', $version, basename($file) . ' is not in $modversion["templates"]');
    }

    #[DataProvider('libraryTemplates')]
    public function testTemplateIncludesOnlyResolvedResources(string $file): void
    {
        // a literal db: include fails before the system module is updated;
        // the resource is passed in a variable (SystemTemplate::resource())
        self::assertDoesNotMatchRegularExpression('/include\s+file\s*=\s*["\']/', (string) file_get_contents($file));
    }
}
