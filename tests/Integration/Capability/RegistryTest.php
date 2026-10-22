<?php

declare(strict_types=1);

namespace App\Tests\Integration\Capability;

use App\Tests\Support\CapabilityLegacyTestCase;
use App\Tests\Support\CapabilitySite;
use App\Tests\Support\TestSite;
use Icms\Capability\Contract\Tagging\CapabilityInterface as Tagging;
use Icms\Capability\Exception\UnavailableException;
use Icms\Capability\Registry;
use Icms\Capability\State;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Runtime lookup and the compiled registry (design D1, D6).
 */
#[RunTestsInSeparateProcesses]
#[Group('spec:capability-registry')]
final class RegistryTest extends CapabilityLegacyTestCase
{
    /**
     * @scenario Active capability lookup
     */
    public function testConsumersGetTheActiveProvidersImplementation(): void
    {
        CapabilitySite::select('tagging', 'advancedtags');
        self::reload();

        $tagging = Registry::capability('tagging');

        self::assertInstanceOf(Tagging::class, $tagging);
        self::assertSame('advancedtags', $tagging->providerModule());
        self::assertTrue(Registry::has('tagging'));
    }

    /**
     * @scenario Same instance in a request
     */
    public function testTheSameInstanceServesTheWholeRequest(): void
    {
        self::assertSame(Registry::capability('tagging'), Registry::capability('tagging'));
        self::assertSame(Registry::capability('tagging'), Registry::find('tagging'));
    }

    /**
     * @scenario Unavailable capability
     */
    public function testAnUnavailableCapabilityFailsWithADedicatedError(): void
    {
        self::assertFalse(Registry::has('workflow'));
        self::assertNull(Registry::find('workflow'));

        CapabilitySite::select('tagging', 'nolongerinstalled');
        self::reload();
        self::assertFalse(Registry::has('tagging'));

        try {
            Registry::capability('tagging');
            self::fail('no exception');
        } catch (UnavailableException $exception) {
            self::assertSame('tagging', $exception->capability);
            self::assertSame(State::Unresolved, $exception->state);
            self::assertStringContainsString('"tagging"', $exception->getMessage());
            self::assertStringContainsString('unresolved', $exception->getMessage());
        }
    }

    /**
     * @scenario Listing capabilities
     */
    public function testEveryKnownCapabilityHasAState(): void
    {
        CapabilitySite::select('tagging', 'smarttags');
        $registry = self::reload();

        self::assertSame(['tagging', 'taxonomy'], Registry::capabilities());
        self::assertSame(State::Active, Registry::state('tagging'));
        self::assertSame(['smarttags', 'advancedtags', 'knowledgemanager'], array_keys($registry->snapshot()->providers('tagging')));
        self::assertSame('smarttags', $registry->activeProviderOf('tagging'));
        self::assertSame(State::Unavailable, Registry::state('workflow'));
    }

    /**
     * @scenario Missing registry cache
     */
    public function testAMissingSnapshotIsRebuiltBeforeTheLookup(): void
    {
        @unlink(CapabilitySite::snapshotFile());
        self::reload();

        self::assertSame(State::Active, Registry::state('tagging'));
        self::assertFileExists(CapabilitySite::snapshotFile());
    }

    /**
     * @scenario Provider file added without a module update
     */
    public function testProviderFilesAreOnlyReadByARebuild(): void
    {
        $file = CapabilitySite::moduleDir('news') . '/capability/Taxonomy.php';
        @mkdir(dirname($file));
        file_put_contents($file, "<?php\n// declares no provider class\n");

        try {
            $registry = self::reload();
            self::assertArrayNotHasKey('news', $registry->snapshot()->rejected(), 'requests keep the built registry');

            $snapshot = Registry::rebuild();
            self::assertStringContainsString('does not declare the class mod_news_TaxonomyCapability', $snapshot->rejected()['news'][0]['reason']);
        } finally {
            @unlink($file);
            @rmdir(dirname($file));
            Registry::rebuild();
        }
    }

    /**
     * @scenario Request without capability use
     */
    public function testARequestWithoutCapabilityUseCreatesNoProvider(): void
    {
        $mimetype = icms_getModuleHandler('mimetype', 'system')->get(1);

        $queries = $this->queriesOf(static fn () => $mimetype->getForm('caption', 'addmimetype')->render());

        self::assertFalse(class_exists('mod_smarttags_TaggingCapability', false), 'no provider file was loaded');
        self::assertFalse(class_exists('mod_taxonomy_TaxonomyCapability', false));
        self::assertLessThanOrEqual(1, count(array_filter($queries, static fn (string $sql): bool => str_contains($sql, 'capability'))), 'at most the assignment matrix is read');
    }

    public function testTheSnapshotLivesInTheCacheDirectory(): void
    {
        self::assertSame(TestSite::legacyRoot() . '/cache/capability-registry.php', str_replace('\\', '/', ICMS_CACHE_PATH) . '/capability-registry.php');
    }
}
