<?php

declare(strict_types=1);

namespace App\Tests\Unit\Ipf;

use App\Ipf\Compiler\ArtifactGenerator;
use App\Ipf\Compiler\PhpExporter;
use App\Ipf\Definition\DefinitionReader;
use App\Ipf\Definition\ResourceDefinition;
use App\Ipf\Resource\ResourceRegistration;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Fixtures.php';

/**
 * Generated artifacts: deterministic, valid PHP, rules derived from the
 * definition, unsupported controls reported instead of coerced.
 */
final class ArtifactGeneratorTest extends TestCase
{
    public function testOutputIsDeterministic(): void
    {
        $first = $this->generate(new FixtureEntity());
        $second = $this->generate(new FixtureEntity());

        self::assertSame($first, $second);
        self::assertSame(['Fixture.php', 'FixtureDescriptor.php', 'FixturePolicy.php', 'FixtureRequest.php', 'FixtureSchema.php'], array_keys($first));
    }

    public function testFilesAreMarkedValidPhp(): void
    {
        foreach ($this->generate(new FixtureEntity()) as $name => $code) {
            self::assertStringContainsString('GENERATED FILE - DO NOT EDIT.', $code, $name);
            self::assertStringContainsString('Source:    ' . FixtureHandler::class, $code, $name);
            self::assertStringContainsString('namespace Modules\System\Generated\Fixture;', $code, $name);
            token_get_all($code, TOKEN_PARSE);
            self::assertStringNotContainsString("\r", $code);
        }
    }

    public function testRulesComeFromTheDefinition(): void
    {
        $request = $this->generate(new FixtureEntity())['FixtureRequest.php'];

        self::assertStringContainsString("'name' => [\n                'sometimes',\n                'required',\n                'string',\n                'max:60',\n            ],", $request);
        self::assertStringContainsString("'dirname.*' => [\n                'ipf_choice:system.fixture,dirname',\n            ],", $request);
        self::assertStringContainsString("'use_fixture.*' => [\n                'ipf_group',\n            ],", $request);
        self::assertStringContainsString("'name.max' => [\n                '_XOBJ_ERR_SHORTERTHAN',\n                'name',\n                60,\n            ],", $request);
        self::assertStringNotContainsString("'fixtureid'", $request, 'the key is never written from the form');
        self::assertStringNotContainsString("'settings'", $request, 'a field without a form element is not validated');
    }

    public function testAnUnsupportedControlLeavesTheResourceOnTheLegacyForm(): void
    {
        $entity = new FixtureEntity();
        $entity->controls['types'] = ['name' => 'upload'];
        $files = $this->generate($entity);

        self::assertArrayNotHasKey('FixtureRequest.php', $files, 'no FormRequest that would skip the upload');
        self::assertStringContainsString("'validation' => false,", $files['FixtureDescriptor.php']);
        self::assertStringContainsString("'blade' => false,", $files['FixtureDescriptor.php']);
        self::assertStringContainsString("'types' => 'upload',", $files['FixtureDescriptor.php']);
    }

    public function testTheSchemaFollowsTheLegacyUpdaterTypes(): void
    {
        $schema = $this->generate(new FixtureEntity())['FixtureSchema.php'];

        self::assertStringContainsString("'table' => 'system_fixture',", $schema);
        self::assertStringContainsString("'fixtureid' => [\n                'family' => 'int',\n                'nullable' => false,\n                'default' => null,\n                'autoIncrement' => true,", $schema);
        self::assertStringContainsString("'dirname' => [\n                'family' => 'text',", $schema);
        self::assertStringContainsString("'name' => [\n                'family' => 'varchar',\n                'nullable' => false,\n                'default' => '',", $schema);
    }

    public function testTheModelMapsTheLegacyTable(): void
    {
        $model = $this->generate(new FixtureEntity())['Fixture.php'];

        self::assertStringContainsString("protected \$table = 'system_fixture';", $model);
        self::assertStringContainsString("'dirname' => 'App\\\\Ipf\\\\Eloquent\\\\Casts\\\\PipeList',", $model);
        self::assertStringContainsString("'settings' => 'App\\\\Ipf\\\\Eloquent\\\\Casts\\\\LegacySerialized',", $model);
    }

    public function testExporterIsStable(): void
    {
        self::assertSame("[\n    'a' => 1,\n    'b' => [\n        true,\n        null,\n        1.0,\n    ],\n]", PhpExporter::export(['a' => 1, 'b' => [true, null, 1.0]]));
        self::assertSame("'it''s'", str_replace("\\'", "''", PhpExporter::export("it's")));
    }

    /**
     * @return array<string, string>
     */
    private function generate(object $entity): array
    {
        return (new ArtifactGenerator())->generate($this->definition($entity), new ResourceRegistration('system', 'fixture', 'module:system/fixture', 'Modules\System', '/tmp/modules/system'));
    }

    private function definition(object $entity): ResourceDefinition
    {
        return (new DefinitionReader(__DIR__, 'icms_'))->read(new FixtureHandler(), $entity);
    }
}
