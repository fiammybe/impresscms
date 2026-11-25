<?php

declare(strict_types=1);

namespace App\Tests\Unit\Ipf;

use App\Ipf\Definition\DataType;
use App\Ipf\Definition\DefinitionReader;
use App\Ipf\Definition\OptionProvider;
use App\Ipf\Definition\UnsupportedDefinitionException;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Fixtures.php';

/**
 * The canonical definition read from IPF metadata: what is kept, the
 * defaults of the legacy form, and what is refused instead of dropped.
 */
final class DefinitionReaderTest extends TestCase
{
    public function testFieldsKeepTheIpfSemantics(): void
    {
        $definition = $this->read(new FixtureHandler(), new FixtureEntity());

        self::assertSame('system.fixture', $definition->key());
        self::assertSame('system_fixture', $definition->table, 'the installation prefix is not part of the definition');
        self::assertSame('fixtureid', $definition->primaryKey);
        self::assertSame(['fixtureid', 'extension', 'types', 'name', 'dirname', 'parentid', 'settings'], array_keys($definition->fields));

        $name = $definition->field('name');
        self::assertSame(DataType::TXTBOX, $name->type);
        self::assertTrue($name->required);
        self::assertSame(60, $name->maxLength);
        self::assertSame('Name', $name->caption);
        self::assertSame('The extension', $definition->field('extension')->description);
        self::assertSame(0, $definition->field('parentid')->default);
    }

    public function testControlsFollowTheLegacyForm(): void
    {
        $definition = $this->read(new FixtureHandler(), new FixtureEntity());

        self::assertSame('hidden', $definition->field('fixtureid')->control?->name, 'the key is a hidden field');
        self::assertSame('text', $definition->field('extension')->control?->name);
        self::assertFalse($definition->field('extension')->control?->explicit);
        self::assertSame('textarea', $definition->field('types')->control?->name);
        self::assertNull($definition->field('settings')->control, 'an array without a control has no element');
        self::assertSame('select', $definition->field('parentid')->control?->name, 'a control without a name is a select');

        $dirname = $definition->field('dirname')->control;
        self::assertSame('selectmulti', $dirname?->name);
        self::assertTrue($dirname?->isMultiple());
        self::assertSame(['kind' => OptionProvider::ITEM_HANDLER, 'handler' => 'icms_module', 'method' => 'getActive'], $dirname?->options?->toArray());
    }

    public function testAnItemHandlerIsNeverARelation(): void
    {
        $definition = $this->read(new FixtureHandler(), new FixtureEntity());

        self::assertSame(OptionProvider::ITEM_HANDLER, $definition->field('parentid')->control?->options?->kind);
        self::assertSame([], $definition->relations);
    }

    public function testRelationsAreExplicit(): void
    {
        $definition = $this->read(new RelatedFixtureHandler(), new FixtureEntity());

        self::assertCount(1, $definition->relations);
        self::assertSame('parentid', $definition->relations[0]->field);
        self::assertSame('system.fixture', $definition->relations[0]->target);
        self::assertSame('belongsTo', $definition->relations[0]->cardinality);
    }

    public function testListAndLegacyOperationsComeFromTheAttributes(): void
    {
        $definition = $this->read(new FixtureHandler(), new FixtureEntity());

        self::assertSame(['name', 'extension'], array_column((array) $definition->list?->columns, 'field'));
        self::assertSame(150, $definition->list?->columns[0]['width']);
        self::assertSame(['name', 'extension'], $definition->list?->quickSearch);
        self::assertSame('fixtureid', $definition->list?->sortBy, 'the default sort is the identifier, as Icms\Ipf\View\Table');
        self::assertSame(['fct' => 'fixture'], $definition->legacy?->match);
        self::assertSame('addfixture', $definition->legacy?->save);
        self::assertSame(['use_fixture'], array_map(static fn ($permission): string => $permission->name, $definition->permissions));
    }

    public function testProvenanceNamesTheDeclaration(): void
    {
        $definition = $this->read(new FixtureHandler(), new FixtureEntity());

        self::assertSame(FixtureHandler::class, $definition->provenance->handlerClass);
        self::assertSame(FixtureEntity::class, $definition->provenance->entityClass);
        self::assertSame('Fixtures.php', $definition->provenance->file);
        self::assertGreaterThan(0, $definition->provenance->line);
    }

    public function testUnknownVariableKeysAreRefused(): void
    {
        $entity = new FixtureEntity();
        $entity->vars['name']['validator'] = 'something';

        $this->expectException(UnsupportedDefinitionException::class);
        $this->expectExceptionMessageMatches('/Unknown variable keys validator in .*"name"/');
        $this->read(new FixtureHandler(), $entity);
    }

    public function testUnknownControlsAreRefused(): void
    {
        $entity = new FixtureEntity();
        $entity->controls['types'] = ['name' => 'wysiwyg'];

        $this->expectException(UnsupportedDefinitionException::class);
        $this->expectExceptionMessageMatches('/Unknown control "wysiwyg" in .*"types"/');
        $this->read(new FixtureHandler(), $entity);
    }

    public function testASelectWithoutChoicesIsRefused(): void
    {
        $entity = new FixtureEntity();
        $entity->controls['types'] = ['name' => 'select'];

        $this->expectException(UnsupportedDefinitionException::class);
        $this->read(new FixtureHandler(), $entity);
    }

    public function testMetadataNamingAnUnknownVariableIsRefused(): void
    {
        $this->expectException(UnsupportedDefinitionException::class);
        $this->expectExceptionMessageMatches('/unknown variable "nosuchfield"/');
        $this->read(new BrokenListFixtureHandler(), new FixtureEntity());
    }

    public function testATableWithoutThePrefixIsRefused(): void
    {
        $handler = new FixtureHandler();
        $handler->table = 'other_system_fixture';

        $this->expectException(UnsupportedDefinitionException::class);
        $this->read($handler, new FixtureEntity());
    }

    private function read(object $handler, object $entity): \App\Ipf\Definition\ResourceDefinition
    {
        return (new DefinitionReader(__DIR__, 'icms_'))->read($handler, $entity);
    }
}
