<?php

declare(strict_types=1);

namespace App\Tests\Integration\Ipf;

use App\Ipf\Metadata\DefinitionRegistry;
use App\Ipf\Metadata\EntityDefinition;
use App\Ipf\Metadata\UnknownObjectException;
use App\Legacy\LegacyRuntime;
use App\Tests\Support\LegacyWebTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * The description of an IPF object against the real mimetype entity and its
 * handler (design D2).
 */
#[RunTestsInSeparateProcesses]
#[Group('spec:ipf-metadata')]
final class IpfMetadataTest extends LegacyWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        static::getContainer()->get(LegacyRuntime::class)->bootTo('online');
    }

    /**
     * @scenario Mimetype object
     */
    public function testDescriptionIsTheObjectsOwnDefinition(): void
    {
        $definition = self::definitions()->get('system', 'mimetype');
        $handler = self::mimetypes();
        $vars = $handler->create()->vars;

        self::assertSame('system', $definition->module);
        self::assertSame('mimetype', $definition->item);
        self::assertSame($handler->table, $definition->table);
        self::assertSame('mimetypeid', $definition->keyField);
        self::assertSame('name', $definition->summaryField);
        self::assertSame(EntityDefinition::SOURCE_IPF, $definition->source);
        self::assertSame(['mimetypeid', 'extension', 'types', 'name', 'dirname'], array_keys($definition->fields));

        foreach ($definition->fields as $name => $field) {
            self::assertSame((int) $vars[$name]['data_type'], $field->dataType, $name);
            self::assertSame((bool) $vars[$name]['required'], $field->required, $name);
            self::assertTrue($field->persistent, $name);
        }

        self::assertSame(XOBJ_DTYPE_SIMPLE_ARRAY, $definition->field('dirname')->dataType);
        self::assertSame(_CO_ICMS_MIMETYPE_EXTENSION, $definition->field('extension')->caption);
        self::assertSame(_CO_ICMS_MIMETYPE_EXTENSION_DSC, $definition->field('extension')->description);
        self::assertSame('selectmulti', $definition->field('dirname')->control['name'] ?? null);
        self::assertNull($definition->field('name')->control);
        self::assertSame(['use_extension'], array_column($definition->permissions, 'name'));
        self::assertSame($definition, self::definitions()->forHandler($handler), 'one description per object');
    }

    /**
     * @scenario Unknown object
     */
    public function testUnknownObjectFailsNamingModuleAndItem(): void
    {
        foreach ([['system', 'nosuchitem'], ['nosuchmodule', 'mimetype']] as [$module, $item]) {
            try {
                self::definitions()->get($module, $item);
                self::fail("A description of {$module}/{$item} was built.");
            } catch (UnknownObjectException $e) {
                self::assertStringContainsString("\"{$module}\"", $e->getMessage());
                self::assertStringContainsString("\"{$item}\"", $e->getMessage());
            }
        }
    }

    /**
     * @scenario Legacy access keeps working
     */
    public function testRequestingADescriptionChangesNothing(): void
    {
        $handler = self::mimetypes();
        $object = $handler->get(124);
        $before = [$object->vars, $handler->_itemname, $handler->keyName, $handler->getPermissions(), self::rowCount()];

        self::definitions()->get('system', 'mimetype');

        self::assertSame($handler, self::mimetypes(), 'legacy code keeps its handler instance');
        self::assertSame($before, [$object->vars, $handler->_itemname, $handler->keyName, $handler->getPermissions(), self::rowCount()]);
    }

    private static function rowCount(): int
    {
        return (int) self::mimetypes()->getCount();
    }

    private static function definitions(): DefinitionRegistry
    {
        return static::getContainer()->get(DefinitionRegistry::class);
    }

    private static function mimetypes(): object
    {
        return static::getContainer()->get('icms.module_handlers')->get('system', 'mimetype');
    }
}
