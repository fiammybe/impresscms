<?php

/**
 * Provider discovery by naming convention (design D5, D6)
 *
 * @package icms\tests
 */

declare(strict_types=1);

namespace Icms\Tests\Unit\Capability;

use Icms\Capability\Compiler;
use Icms\Capability\RegistrySnapshot;
use Icms\Capability\Ui\FormContributorInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('spec:capability-registry')]
final class CompilerTest extends TestCase
{
    private const MODULES = __DIR__ . '/../../Fixtures/capability-modules';

    private static ?RegistrySnapshot $snapshot = null;

    /**
     * @scenario Module providing two capabilities
     */
    public function testOneModuleProvidesTwoCapabilities(): void
    {
        $snapshot = self::snapshot();

        self::assertSame(['tagging', 'taxonomy'], $snapshot->capabilitiesOf('alpha'));
        self::assertSame('mod_alpha_TaggingCapability', $snapshot->provider('tagging', 'alpha')['class']);
        self::assertSame('mod_alpha_TaxonomyCapability', $snapshot->provider('taxonomy', 'alpha')['class']);
        self::assertStringEndsWith('/alpha/capability/Tagging.php', $snapshot->provider('tagging', 'alpha')['file']);
    }

    /**
     * @scenario Two modules providing the same capability
     */
    public function testTwoModulesProvideTheSameCapability(): void
    {
        self::assertSame(['alpha', 'beta'], self::snapshot()->compatibleProviders('tagging'));
    }

    /**
     * @scenario UI contributions detected from the class
     */
    public function testUiContributionsAreTheContractsTheClassImplements(): void
    {
        $snapshot = self::snapshot();

        self::assertSame([FormContributorInterface::class], $snapshot->provider('tagging', 'beta')['ui']);
        self::assertSame(Compiler::UI_CONTRACTS, $snapshot->provider('tagging', 'alpha')['ui']);
        self::assertSame([], $snapshot->provider('taxonomy', 'alpha')['ui']);
    }

    /**
     * @scenario Invalid provider class
     */
    public function testInvalidProviderFilesAreRejectedWithTheirReason(): void
    {
        $reasons = [];

        foreach (self::snapshot()->rejected() as $module => $rejections) {
            foreach ($rejections as $rejection) {
                $reasons[$module . '/' . basename($rejection['file'])] = $rejection['reason'];
            }
        }

        self::assertStringContainsString('"comments" is not a known capability', $reasons['gamma/Comments.php']);
        self::assertStringContainsString('does not declare the class mod_gamma_TaxonomyCapability', $reasons['gamma/Taxonomy.php']);
        self::assertStringContainsString('does not implement Icms\\Capability\\Contract\\Tagging\\CapabilityInterface', $reasons['delta/Tagging.php']);
        self::assertStringContainsString('could not be loaded: broken on purpose', $reasons['epsilon/Tagging.php']);
        self::assertStringContainsString('must not require arguments', $reasons['eta/Tagging.php']);
        self::assertCount(5, $reasons);

        // the other providers of these modules and of other modules are kept
        self::assertSame([], self::snapshot()->capabilitiesOf('gamma'));
        self::assertNotNull(self::snapshot()->provider('tagging', 'alpha'));
    }

    /**
     * @scenario Other files in the directory
     */
    public function testFilesThatAreNotPhpAreIgnored(): void
    {
        $rejected = self::snapshot()->rejected();

        self::assertArrayNotHasKey('alpha', $rejected);
        self::assertArrayNotHasKey('plain', $rejected);
        self::assertSame([], self::snapshot()->capabilitiesOf('plain'));
    }

    public function testIncompatibleProvidersAreListedWithTheReason(): void
    {
        $zeta = self::snapshot()->provider('tagging', 'zeta');

        self::assertNotNull($zeta);
        self::assertFalse($zeta['compatible']);
        self::assertSame('major version 0 ≠ 1', $zeta['reason']);
        self::assertNotContains('zeta', self::snapshot()->compatibleProviders('tagging'));
    }

    /**
     * @scenario Provider module deactivated
     */
    public function testOnlyTheGivenActiveModulesAreScanned(): void
    {
        $snapshot = (new Compiler(self::MODULES))->compile(['beta' => self::module()]);

        self::assertSame(['beta'], $snapshot->compatibleProviders('tagging'));
        self::assertSame([], $snapshot->providers('taxonomy'));
        self::assertSame(['beta'], $snapshot->modules());
    }

    public function testObjectTypesAndConsumerDeclarationsAreRecorded(): void
    {
        $snapshot = (new Compiler(self::MODULES))->compile([
            'news' => self::module(['article', 'category'], [
                'article' => ['default' => ['tagging', 'taxonomy']],
                'category' => ['unsupported' => ['tagging']],
            ]),
            'system' => self::module(['mimetype', 'capabilityassignment', 'capabilityprovider']),
        ]);

        self::assertSame(['news/article', 'news/category', 'system/mimetype'], $snapshot->objectTypes());
        self::assertSame(['tagging', 'taxonomy'], $snapshot->defaultsFor('news/article'));
        self::assertTrue($snapshot->isUnsupported('news/category', 'tagging'));
        self::assertFalse($snapshot->isUnsupported('news/article', 'tagging'));
    }

    public function testTheSnapshotSurvivesExport(): void
    {
        $exported = eval('return ' . var_export(self::snapshot()->toArray(), true) . ';');

        self::assertEquals(self::snapshot(), new RegistrySnapshot($exported));
    }

    private static function snapshot(): RegistrySnapshot
    {
        if (self::$snapshot !== null) {
            return self::$snapshot;
        }

        $modules = [];

        foreach (['alpha', 'beta', 'gamma', 'delta', 'epsilon', 'zeta', 'eta', 'plain'] as $dirname) {
            $modules[$dirname] = self::module();
        }

        return self::$snapshot = (new Compiler(self::MODULES))->compile($modules);
    }

    /**
     * @param list<string> $objectItems
     * @param array<string, mixed> $support
     * @return array{name: string, version: string, objectItems: list<string>, support: array<string, mixed>}
     */
    private static function module(array $objectItems = [], array $support = []): array
    {
        return ['name' => 'Fixture', 'version' => '1.0', 'objectItems' => $objectItems, 'support' => $support];
    }
}
