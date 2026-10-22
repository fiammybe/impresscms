<?php

declare(strict_types=1);

namespace App\Tests\Http\Capability;

use App\Tests\Support\CapabilityHttpTestCase;
use App\Tests\Support\CapabilitySite;
use App\Tests\Support\TestSite;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\DomCrawler\Crawler;

/**
 * The capabilities page of the system administration: providers, switching,
 * the assignment matrix, the dashboard warning and the uninstall warning
 * (design D8, D9).
 */
#[Group('spec:capability-provider-selection')]
final class CapabilitiesPageTest extends CapabilityHttpTestCase
{
    private const PAGE = '/modules/system/admin.php?fct=capabilities';

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        CapabilitySite::install('oldtags');
    }

    protected function setUp(): void
    {
        parent::setUp();
        CapabilitySite::select('tagging', 'smarttags');
        CapabilitySite::select('taxonomy', 'taxonomy');
        CapabilitySite::assign(['news/article' => ['tagging' => true]]);
    }

    /**
     * @scenario Viewing providers
     */
    public function testProvidersAreListedWithTheActiveOneMarked(): void
    {
        $this->loginAsWebmaster();
        $page = $this->page(self::PAGE);

        self::assertSame('active', $page->filterXPath('//div[@id="capability-tagging"]')->attr('data-state'));
        self::assertSame(['smarttags', 'advancedtags', 'knowledgemanager', 'oldtags'], self::providers($page, 'tagging'));
        self::assertSame(['smarttags'], $page->filterXPath('//div[@id="capability-tagging"]//tr[@data-active="1"]')->each(static fn (Crawler $row): string => (string) $row->attr('data-provider')));
        self::assertSame(['taxonomy', 'knowledgemanager'], self::providers($page, 'taxonomy'));
        self::assertStringContainsString(
            '/modules/advancedtags/admin/tags.php',
            (string) $page->filterXPath('//tr[@data-provider="advancedtags"]//a[contains(@class, "provider-admin")]')->attr('href'),
            'a provider with its own administration is linked',
        );
    }

    /**
     * @scenario Incompatible provider
     */
    public function testAnIncompatibleProviderIsListedButCannotBeChosen(): void
    {
        $this->loginAsWebmaster();
        $page = $this->page(self::PAGE);
        $row = $page->filterXPath('//div[@id="capability-tagging"]//tr[@data-provider="oldtags"]');

        self::assertSame('disabled', $row->filterXPath('//input[@type="radio"]')->attr('disabled'));
        self::assertStringContainsString('major version 0 ≠ 1', $row->text());

        $form = $page->filterXPath('//div[@id="capability-tagging"]//form')->form();
        $values = $form->getPhpValues();
        $values['provider'] = 'oldtags';
        $this->request('POST', self::PAGE, $values, ['HTTP_REFERER' => static::url(self::PAGE)]);

        self::assertSame('smarttags', CapabilitySite::selections()['tagging']);
    }

    /**
     * @scenario Activating a second provider
     * @scenario Confirmed switch
     * @scenario Switching back
     */
    public function testASwitchIsConfirmedAndTheDataStaysWithItsProvider(): void
    {
        $this->loginAsWebmaster();
        $articleId = $this->createArticle('Switched article', ['capability[tagging][tags]' => 'kept']);

        $confirmation = $this->choose('tagging', 'advancedtags');
        self::assertSame(['news/article'], $confirmation->filterXPath('//ul[@id="capability-switch-types"]/li')->each(static fn (Crawler $li): string => $li->text()));
        self::assertStringContainsString('is not moved', $confirmation->text());
        self::assertSame('smarttags', CapabilitySite::selections()['tagging'], 'nothing changes before the confirmation');

        $response = $this->submit($confirmation->filterXPath('//form')->form());
        self::assertSame(302, $response->getStatusCode(), self::serverLog());
        self::assertSame('advancedtags', CapabilitySite::selections()['tagging']);
        self::assertSame(['kept'], self::storedTags('smarttags', $articleId), 'the previous provider\'s data is untouched');
        self::assertSame('', $this->articleForm($articleId)->get('capability[tagging][tags]')->getValue(), 'and not visible through the new provider');

        $this->submit($this->choose('tagging', 'smarttags')->filterXPath('//form')->form());
        self::assertSame('smarttags', CapabilitySite::selections()['tagging']);
        self::assertSame('kept', $this->articleForm($articleId)->get('capability[tagging][tags]')->getValue());
    }

    /**
     * @scenario Switch without a valid token
     */
    public function testASwitchWithoutAValidTokenIsRejected(): void
    {
        $this->loginAsWebmaster();

        $response = $this->request('POST', self::PAGE, [
            'op' => 'select_ok',
            'capability' => 'tagging',
            'provider' => 'advancedtags',
        ], ['HTTP_REFERER' => static::url(self::PAGE)]);

        self::assertSame(302, $response->getStatusCode(), self::serverLog());
        self::assertSame('smarttags', CapabilitySite::selections()['tagging']);
    }

    /**
     * @scenario Access denied
     */
    public function testAdministratorsWithoutTheFunctionAreDenied(): void
    {
        $this->loginAs(TestSite::MEDIAADMIN_UNAME, TestSite::MEDIAADMIN_PASS);

        $response = $this->request('GET', self::PAGE);

        self::assertSame(302, $response->getStatusCode(), self::serverLog());
        self::assertStringNotContainsString('capability-tagging', $response->getContent());
    }

    /**
     * @scenario Assignment of an unavailable capability
     * @scenario Unsupported capability
     */
    #[Group('spec:capability-assignment')]
    public function testTheMatrixDisablesUnsupportedCellsAndSaves(): void
    {
        $this->loginAsWebmaster();
        $matrix = $this->page(self::PAGE . '&op=assignments');

        self::assertSame('disabled', $matrix->filterXPath('//input[@name="assign[news/category][tagging]"]')->attr('disabled'), 'news declares tagging unsupported for categories');
        self::assertNull($matrix->filterXPath('//input[@name="assign[news/article][taxonomy]"]')->attr('disabled'));
        self::assertSame('checked', $matrix->filterXPath('//input[@name="assign[news/article][tagging]"]')->attr('checked'));

        $form = $matrix->filterXPath('//form[@id="capability-assignments"]')->form();
        $form['assign[news/article][taxonomy]']->tick();
        $form['assign[news/article][tagging]']->untick();
        $this->submit($form);

        self::assertSame(['news/article' => ['tagging' => 0, 'taxonomy' => 1]], self::cells('news/article'));

        $values = $this->page(self::PAGE . '&op=assignments')->filterXPath('//form[@id="capability-assignments"]')->form()->getPhpValues();
        $values['assign']['news/category']['tagging'] = '1';
        $this->request('POST', self::PAGE, $values, ['HTTP_REFERER' => static::url(self::PAGE)]);

        self::assertSame([], self::cells('news/category'), 'an attempt to enable an unsupported cell is rejected');
    }

    /**
     * @scenario Active provider uninstalled while an alternative exists
     */
    public function testTheDashboardWarnsAboutAProviderThatIsGone(): void
    {
        CapabilitySite::select('tagging', 'nolongerinstalled');
        $this->loginAsWebmaster();

        $warning = $this->page('/admin.php')->filterXPath('//div[@id="capability-conflicts"]');

        self::assertCount(1, $warning);
        self::assertStringContainsString('nolongerinstalled', $warning->text());

        $page = $this->page(self::PAGE);
        self::assertSame('unresolved', $page->filterXPath('//div[@id="capability-tagging"]')->attr('data-state'));
        self::assertCount(0, $page->filterXPath('//div[@id="capability-tagging"]//tr[@data-active="1"]'), 'no other provider is activated');
    }

    /**
     * @scenario Uninstall warning
     */
    public function testTheUninstallConfirmationNamesTheAssignedCapabilities(): void
    {
        $this->loginAsWebmaster();

        $warning = $this->page('/modules/system/admin.php?fct=modulesadmin&op=uninstall&module=smarttags')->filterXPath('//div[@id="capability-uninstall-warning"]');

        self::assertCount(1, $warning);
        self::assertStringContainsString('Tagging: news/article', $warning->text());
        self::assertCount(0, $this->page('/modules/system/admin.php?fct=modulesadmin&op=uninstall&module=advancedtags')->filterXPath('//div[@id="capability-uninstall-warning"]'));
    }

    public function testTheRegistryIsRebuiltOnRequest(): void
    {
        $this->loginAsWebmaster();
        @unlink(CapabilitySite::snapshotFile());

        $form = $this->page(self::PAGE)->filterXPath('//form[@id="capability-rebuild"]')->form();
        $response = $this->submit($form);

        self::assertSame(302, $response->getStatusCode(), self::serverLog());
        self::assertFileExists(CapabilitySite::snapshotFile());
    }

    /** Choose a provider on the page and return the response page (a confirmation, or the redirect target). */
    private function choose(string $capability, string $provider): Crawler
    {
        $form = $this->page(self::PAGE)->filterXPath("//div[@id=\"capability-{$capability}\"]//form")->form();
        $form['provider']->select($provider);
        $response = $this->submit($form);
        self::assertSame(200, $response->getStatusCode(), 'a confirmation page' . self::serverLog());

        return new Crawler($response->getContent(), static::url(self::PAGE));
    }

    /** @return list<string> */
    private static function providers(Crawler $page, string $capability): array
    {
        return $page->filterXPath("//div[@id=\"capability-{$capability}\"]//tr[@data-provider]")->each(static fn (Crawler $row): string => (string) $row->attr('data-provider'));
    }

    /** @return array<string, array<string, int>> */
    private static function cells(string $objectType): array
    {
        $statement = TestSite::connection()->prepare('SELECT capability, enabled FROM ' . TestSite::table('system_capabilityassignment') . ' WHERE object_type = ? ORDER BY capability');
        $statement->execute([$objectType]);
        $cells = array_map('intval', $statement->fetchAll(\PDO::FETCH_KEY_PAIR));

        return $cells === [] ? [] : [$objectType => $cells];
    }
}
