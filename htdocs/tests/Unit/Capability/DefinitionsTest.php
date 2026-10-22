<?php

/**
 * The capability catalogue and contract versions (design D3, D8)
 *
 * @package icms\tests
 */

declare(strict_types=1);

namespace Icms\Tests\Unit\Capability;

use Icms\Capability\Contract\BaseInterface;
use Icms\Capability\Contract\Tagging\CapabilityInterface as Tagging;
use Icms\Capability\Contract\Taxonomy\CapabilityInterface as Taxonomy;
use Icms\Capability\Definitions;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('spec:capability-contracts')]
final class DefinitionsTest extends TestCase
{
    public function testTheCoreKnowsTaggingAndTaxonomy(): void
    {
        self::assertSame(['tagging', 'taxonomy'], Definitions::names());
        self::assertSame(Tagging::class, Definitions::contract('tagging'));
        self::assertSame(Taxonomy::class, Definitions::contract('taxonomy'));
        self::assertSame('1.0', Definitions::version('tagging'));
        self::assertSame('1.0', Definitions::version('taxonomy'));
    }

    /**
     * @scenario Consumer without provider dependency
     */
    public function testContractsAreCoreTypesThatExtendTheBaseContract(): void
    {
        foreach (Definitions::names() as $name) {
            $contract = Definitions::contract($name);

            self::assertTrue(interface_exists($contract));
            self::assertTrue(is_subclass_of($contract, BaseInterface::class));
            self::assertStringStartsWith('Icms\\Capability\\Contract\\', $contract);
        }
    }

    public function testUnknownCapabilitiesHaveNoDefinition(): void
    {
        self::assertFalse(Definitions::has('workflow'));

        $this->expectException(InvalidArgumentException::class);

        Definitions::contract('workflow');
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: bool}>
     */
    public static function versions(): array
    {
        return [
            'same version' => ['1.0', '1.0', true],
            'newer minor' => ['1.0', '1.3', true],
            'patch level' => ['1.1', '1.1.4', true],
            'older minor' => ['1.1', '1.0', false],
            'older major' => ['2.0', '1.3', false],
            'newer major' => ['1.0', '2.0', false],
            'not a version' => ['1.0', 'latest', false],
        ];
    }

    /**
     * @scenario Minor extension
     */
    #[DataProvider('versions')]
    public function testProvidersMustMatchTheMajorAndAtLeastTheMinorVersion(string $core, string $provider, bool $compatible): void
    {
        self::assertSame($compatible, Definitions::isCompatible($core, $provider));
        self::assertSame($compatible, Definitions::incompatibility($core, $provider) === null);
    }

    /**
     * @scenario Provider built for an older major version
     */
    #[Group('spec:capability-registry')]
    public function testTheReasonNamesTheMajorVersions(): void
    {
        self::assertSame('major version 1 ≠ 2', Definitions::incompatibility('2.0', '1.3'));
    }
}
