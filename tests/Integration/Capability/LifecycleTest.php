<?php

declare(strict_types=1);

namespace App\Tests\Integration\Capability;

use App\Tests\Support\CapabilityLegacyTestCase;
use App\Tests\Support\CapabilitySite;
use App\Tests\Support\TestSite;
use Icms\Capability\ObjectRef;
use Icms\Capability\Registry;
use Icms\Capability\State;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Provider discovery and selection along the module lifecycle, through the
 * module administration's own functions (design D6, D8). The class starts
 * with news and smarttags installed; the test tells one story.
 */
#[RunTestsInSeparateProcesses]
#[Group('spec:capability-provider-selection')]
final class LifecycleTest extends CapabilityLegacyTestCase
{
    protected static function modules(): array
    {
        return ['news', 'smarttags'];
    }

    /**
     * @scenario First provider installed
     * @scenario Second provider installed
     * @scenario Active provider uninstalled while an alternative exists
     * @scenario Last provider uninstalled
     * @scenario Provider module uninstalled
     * @scenario Provider module deactivated
     * @scenario Default enabled capability
     * @scenario Consumer uninstalled
     */
    #[Group('spec:capability-provider-selection')]
    #[Group('spec:capability-registry')]
    #[Group('spec:capability-assignment')]
    public function testProvidersFollowTheModuleLifecycle(): void
    {
        // the only tagging provider became active when it was installed, and
        // the consumer's default was applied
        self::assertSame(['tagging' => 'smarttags'], CapabilitySite::selections());
        self::assertSame([['news/article', 'tagging', 1, 'default']], self::assignments());

        $article = self::article('Lifecycle article');
        Registry::capability('tagging')->setTags(ObjectRef::fromEntity($article), ['lifecycle']);

        // a second provider does not take over
        CapabilitySite::admin('install', 'advancedtags');
        self::assertSame(['tagging' => 'smarttags'], CapabilitySite::selections());
        self::assertSame(State::Active, self::reload()->stateOf('tagging'));
        self::assertSame(['smarttags', 'advancedtags'], self::reload()->snapshot()->compatibleProviders('tagging'));

        // a deactivated provider is no provider until it is activated again
        CapabilitySite::admin('deactivate', 'smarttags');
        self::assertSame(State::Unresolved, self::reload()->stateOf('tagging'));
        self::assertSame(['advancedtags'], self::reload()->snapshot()->compatibleProviders('tagging'));
        CapabilitySite::admin('activate', 'smarttags');
        self::assertSame('smarttags', self::reload()->activeProviderOf('tagging'), 'the selection survives the deactivation');

        // the active provider uninstalled: unresolved, no automatic switch
        CapabilitySite::admin('uninstall', 'smarttags');
        $registry = self::reload();
        self::assertSame(['tagging' => 'smarttags'], CapabilitySite::selections(), 'the selection row is kept');
        self::assertSame(State::Unresolved, $registry->stateOf('tagging'));
        self::assertNull($registry->activeProviderOf('tagging'));
        self::assertSame(['advancedtags'], $registry->snapshot()->compatibleProviders('tagging'));
        self::assertArrayHasKey('tagging', $registry->conflicts());

        // the last provider uninstalled: unavailable, assignments kept
        CapabilitySite::admin('uninstall', 'advancedtags');
        $registry = self::reload();
        self::assertSame(State::Unavailable, $registry->stateOf('tagging'));
        self::assertSame([], $registry->snapshot()->providers('tagging'));
        self::assertSame([['news/article', 'tagging', 1, 'default']], self::assignments());
        self::assertFalse(Registry::isEnabledFor('news/article', 'tagging'));
    }

    /**
     * @scenario Consumer uninstalled
     * @scenario Provider module uninstalled
     */
    #[Depends('testProvidersFollowTheModuleLifecycle')]
    #[Group('spec:capability-assignment')]
    public function testUninstallingTheConsumerRemovesItsAssignmentsAndData(): void
    {
        CapabilitySite::admin('install', 'smarttags');
        $registry = self::reload();
        self::assertSame('smarttags', $registry->activeProviderOf('tagging'));
        $article = self::article('Consumer article');
        $before = self::rows('smarttags_links', "module = 'news'");
        Registry::capability('tagging')->setTags(ObjectRef::fromEntity($article), ['consumer']);
        self::assertSame($before + 1, self::rows('smarttags_links', "module = 'news'"));

        CapabilitySite::admin('uninstall', 'news');

        self::assertSame([], self::assignments());
        self::assertSame(0, self::rows('smarttags_links', "module = 'news'"), 'the active provider cleared the consumer\'s object types');
        self::assertNotContains('news/article', self::reload()->snapshot()->objectTypes());
    }

    /**
     * @scenario Invalid provider class
     */
    #[Depends('testUninstallingTheConsumerRemovesItsAssignmentsAndData')]
    #[Group('spec:capability-registry')]
    public function testTheModuleAdministrationReportsRejectedProviderFiles(): void
    {
        $file = CapabilitySite::moduleDir('taxonomy') . '/capability/Comments.php';
        file_put_contents($file, "<?php\n");

        try {
            $output = CapabilitySite::admin('install', 'taxonomy');
        } finally {
            @unlink($file);
        }

        self::assertStringContainsString('Capability provider Comments.php ignored: "comments" is not a known capability', html_entity_decode($output, ENT_QUOTES));
        self::assertStringContainsString('installed successfully', $output, 'the module itself still installs');
        self::assertSame('taxonomy', self::reload()->activeProviderOf('taxonomy'), 'and its valid provider is registered');
    }

    /** @return list<array{0: string, 1: string, 2: int, 3: string}> */
    private static function assignments(): array
    {
        $rows = TestSite::connection()->query('SELECT object_type, capability, enabled, source FROM ' . TestSite::table('system_capabilityassignment') . ' ORDER BY object_type, capability');

        return array_map(static fn (array $row): array => [$row[0], $row[1], (int) $row[2], $row[3]], $rows->fetchAll(\PDO::FETCH_NUM));
    }

    private static function rows(string $table, string $where): int
    {
        return (int) TestSite::connection()->query('SELECT COUNT(*) FROM ' . TestSite::table($table) . " WHERE {$where}")->fetchColumn();
    }
}
