<?php

declare(strict_types=1);

namespace App\Tests\Integration\Ipf;

use App\Ipf\Compiler\DefinitionCompiler;
use App\Ipf\Resource\ResourceRegistration;
use App\Ipf\Resource\ResourceRegistry;
use App\Tests\Support\LegacyAppTestCase;
use App\Tests\Support\TestSite;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * The compiler against the real IPF definitions: the committed artifacts
 * match them, output is deterministic, drift is detected, and the explicit
 * list metadata matches what the legacy admin script builds.
 */
#[RunTestsInSeparateProcesses]
#[Group('spec:ipf-definitions')]
final class DefinitionCompilerTest extends LegacyAppTestCase
{
    public function testCommittedArtifactsMatchTheDefinitions(): void
    {
        self::assertSame([], $this->compiler()->check(), 'run `php artisan icms:definitions:compile`');
        self::assertSame(0, Artisan::call('icms:definitions:check'));
    }

    public function testOutputIsDeterministic(): void
    {
        $registration = $this->registry()->get('system.mimetype');

        self::assertSame($this->compiler()->artifacts($registration), $this->compiler()->artifacts($registration));
    }

    public function testDriftIsDetected(): void
    {
        $directory = TestSite::varDir() . '/compiler-' . getmypid();
        $registration = $this->registry()->get('system.mimetype');
        $copy = new ResourceRegistration('system', 'mimetype', $registration->handler, 'Modules\System', $directory);
        $this->registry()->add($copy);

        try {
            self::assertCount(5, $this->compiler()->compile('system'));
            self::assertSame([], $this->compiler()->check('system'));
            self::assertSame([], $this->compiler()->compile('system'), 'compiling again changes nothing');

            $request = "{$directory}/Generated/Mimetype/MimetypeRequest.php";
            file_put_contents($request, str_replace("'required',", "'nullable',", (string) file_get_contents($request)));
            file_put_contents("{$directory}/Generated/Mimetype/MimetypeExtra.php", "<?php\n");
            unlink("{$directory}/Generated/Mimetype/MimetypeSchema.php");

            $drift = $this->compiler()->check('system');
            self::assertSame('differs from the definition', $drift[$request] ?? null);
            self::assertSame('no longer generated', $drift["{$directory}/Generated/Mimetype/MimetypeExtra.php"] ?? null);
            self::assertSame('missing', $drift["{$directory}/Generated/Mimetype/MimetypeSchema.php"] ?? null);
            self::assertSame(1, Artisan::call('icms:definitions:check', ['--module' => 'system']));
        } finally {
            array_map('unlink', glob("{$directory}/Generated/Mimetype/*") ?: []);
            @rmdir("{$directory}/Generated/Mimetype");
            @rmdir("{$directory}/Generated");
            @rmdir($directory);
        }
    }

    public function testTheDefinitionOfTheMimetypeResource(): void
    {
        $definition = $this->compiler()->definition($this->registry()->get('system.mimetype'));

        self::assertSame('system_mimetype', $definition->table);
        self::assertSame(['mimetypeid', 'extension', 'types', 'name', 'dirname'], array_keys($definition->fields));
        self::assertSame('itemHandler', $definition->field('dirname')->control?->options?->kind);
        self::assertSame([], $definition->relations, 'a choice provider is not a relation');
        // quickInitVar() compares the type with a string, so no maxlength reaches the definition
        self::assertNull($definition->field('extension')->maxLength);
        self::assertSame('modules/system/admin/mimetype/class/mimetype.php', $definition->provenance->file);
    }

    /**
     * The list attributes on the handler restate admin/mimetype/main.php;
     * this pins one to the other.
     */
    public function testListMetadataMatchesTheLegacyAdminScript(): void
    {
        $definition = $this->compiler()->definition($this->registry()->get('system.mimetype'));
        $script = (string) file_get_contents(TestSite::legacyRoot() . '/modules/system/admin/mimetype/main.php');

        preg_match_all("/new icms_ipf_view_Column\\('(\\w+)', _GLOBAL_LEFT(?:, (\\d+))?\\)/", $script, $columns, PREG_SET_ORDER);
        self::assertSame(
            array_map(static fn (array $match): array => [$match[1], isset($match[2]) ? (int) $match[2] : null], $columns),
            array_map(static fn (array $column): array => [$column['field'], $column['width']], (array) $definition->list?->columns),
        );

        preg_match("/addQuickSearch\\(array\\(([^)]*)\\)\\)/", $script, $search);
        self::assertSame(array_map(static fn (string $field): string => trim($field, " '"), explode(',', $search[1])), $definition->list?->quickSearch);
        self::assertStringContainsString("addIntroButton('addmimetype', 'admin.php?fct=mimetype&amp;op=mod'", $script);
        self::assertTrue($definition->list?->createButton);
        foreach (['mod', 'addmimetype', 'del'] as $op) {
            self::assertStringContainsString("case \"{$op}\":", $script);
        }
    }

    private function compiler(): DefinitionCompiler
    {
        return $this->app->make(DefinitionCompiler::class);
    }

    private function registry(): ResourceRegistry
    {
        return $this->app->make(ResourceRegistry::class);
    }
}
