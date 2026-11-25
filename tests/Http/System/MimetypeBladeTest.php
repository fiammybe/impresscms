<?php

declare(strict_types=1);

namespace App\Tests\Http\System;

use App\Testing\HostMarker;
use App\Tests\Support\HttpTestCase;
use App\Tests\Support\ServerProcess;
use App\Tests\Support\TestSite;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\DomCrawler\Crawler;

/**
 * The mimetype administration rendered with the IPF Blade components, end to
 * end through its legacy URL, with the legacy repository (the IPF handler).
 *
 * The HTML differs from the legacy page, so these assertions are semantic:
 * the field names, values, choices and captions the legacy form has, the
 * list's rows, sort, search and links under the legacy parameter names, and
 * the effects of saving and deleting on the database.
 *
 * MimetypeEloquentReadTest runs the same cases with reads through the
 * generated Eloquent model.
 */
#[Group('spec:system-mimetype-admin')]
class MimetypeBladeTest extends HttpTestCase
{
    use MimetypePage;

    protected static function createServer(): ServerProcess
    {
        return ServerProcess::forSite(['ICMS_SYSTEM_MIMETYPE_RENDERER' => 'blade']);
    }

    protected function setUp(): void
    {
        if (ServerProcess::routing() !== 3) {
            self::markTestSkipped('The mimetype takeover needs routing level 3 (ICMS_TEST_ROUTING=3).');
        }
        TestSite::restoreSnapshot();
        parent::setUp();
    }

    public function testListShowsTheRowsWithLegacyLinks(): void
    {
        $page = $this->asWebmaster()->page(self::PAGE . '&limitsel=all');

        self::assertSame(['Application Type', 'File Extension', 'Mimetypes'], array_slice($page->filter('table.ipf-list th a')->each(static fn (Crawler $a): string => $a->text()), 0, 3));
        self::assertContains('icmsa', $this->cells($page, 1));
        self::assertStringContainsString('fct=mimetype', (string) $page->filter('a.ipf-create')->attr('href'));
        self::assertStringContainsString('op=mod', (string) $page->filter('a.ipf-create')->attr('href'));

        $row = $page->filter('tr[data-id="' . self::ALPHA . '"]');
        self::assertSame('/modules/system/admin.php?fct=mimetype&op=mod&mimetypeid=124', $row->filter('a.ipf-action-edit')->attr('href'));
        self::assertSame('/modules/system/admin.php?fct=mimetype&op=del&mimetypeid=124', $row->filter('a.ipf-action-delete')->attr('href'));
        self::assertSame('/modules/system/admin.php?mimetype_sortsel=name&mimetype_ordersel=ASC&fct=mimetype', $page->filter('table.ipf-list th a')->first()->attr('href'));
    }

    public function testListSortsAndSearchesWithTheLegacyParameters(): void
    {
        $this->asWebmaster();

        $sorted = $this->cells($this->page(self::PAGE . '&mimetype_sortsel=extension&mimetype_ordersel=DESC&limitsel=all'), 1);
        $expected = $sorted;
        rsort($expected, SORT_STRING | SORT_FLAG_CASE);
        self::assertSame($expected, $sorted);

        $found = $this->page(self::PAGE . '&quicksearch_SystemMimetype=x-icms-beta');
        self::assertSame(['icmsb'], $this->cells($found, 1));
        self::assertSame('x-icms-beta', $found->filter('#quicksearch_SystemMimetype')->attr('value'));
    }

    public function testEditFormPostsTheLegacyFieldsToTheLegacyUrl(): void
    {
        [$crawler, $response] = $this->asWebmaster()->openForm(self::PAGE . '&op=mod&mimetypeid=' . self::ALPHA);
        self::assertSame(200, $response->getStatusCode(), self::serverLog());
        self::assertSame(self::ROUTE, $response->getHeader(HostMarker::ROUTE_HEADER));

        $form = $crawler->filter('form#addmimetype');
        self::assertSame('/modules/system/admin.php', $form->attr('action'));
        self::assertSame('icmsa', $form->filter('input[name="extension"]')->attr('value'));
        self::assertSame('application/x-icms-alpha', $form->filter('textarea[name="types"]')->text());
        self::assertSame((string) self::ALPHA, $form->filter('input[name="mimetypeid"]')->attr('value'));
        self::assertSame('addmimetype', $form->filter('input[name="op"]')->attr('value'));
        self::assertSame('mimetype', $form->filter('input[name="fct"]')->attr('value'));
        self::assertNotEmpty($form->filter('input[name="XOOPS_TOKEN_REQUEST"]')->attr('value'));
        self::assertNotEmpty($form->filter('input[name="icms_page_before_form"]')->attr('value'));
        self::assertSame(['system'], $form->filter('select[name="dirname[]"] option[selected]')->each(static fn (Crawler $option): string => (string) $option->attr('value')));
        // groups in the order of their names, as the legacy permission control lists them
        $groups = $form->filter('select[name="use_extension[]"] option[selected]')->each(static fn (Crawler $option): string => (string) $option->attr('value'));
        sort($groups);
        self::assertSame(['1', '2'], $groups);
        self::assertSame(['File Extension', 'Mimetypes', 'Application Type', 'Select Modules allowed to use this mimetype.', 'View permission'], $form->filter('tr.ipf-field label')->each(static fn (Crawler $label): string => trim($label->text())));
        self::assertCount(0, $form->filter('input[name="mimetypeid"][type="text"]'), 'the key is hidden');
    }

    public function testCreatingThroughTheBladeFormStoresTheMimetypeAndItsGroups(): void
    {
        $response = $this->asWebmaster()->createMimetype();

        self::assertSame(302, $response->getStatusCode(), self::serverLog());
        $row = self::mimetypeRow('icmsd');
        self::assertNotNull($row);
        self::assertSame('application/x-icms-delta' . self::HTML_FILTER_MARKS, $row['types']);
        self::assertSame('system', $row['dirname']);
        self::assertSame([1, 2], self::useExtensionGroups((int) $row['mimetypeid']));
    }

    public function testUpdatingKeepsAnUnsentMultiSelectAndReplacesTheGroups(): void
    {
        $response = $this->asWebmaster()->updateMimetype();

        self::assertSame(302, $response->getStatusCode(), self::serverLog());
        $row = self::mimetypeRow('icmsa');
        self::assertSame('ICMS Fixture Alpha, renamed', $row['name'] ?? null);
        self::assertSame('system', $row['dirname'] ?? null, 'an empty multi-select is not posted, so it is kept');
        self::assertSame('application/x-icms-alpha' . self::HTML_FILTER_MARKS, $row['types'] ?? null, 'the form edits the text without the filter marks, so saving does not repeat them');
        self::assertSame([3], self::useExtensionGroups(self::ALPHA));
    }

    public function testDeletingAsksThenDeletesTheMimetypeAndItsGroups(): void
    {
        [$crawler] = $this->asWebmaster()->openDeleteConfirmation(self::BETA);
        self::assertStringContainsString((string) self::BETA, $crawler->filter('div.confirmMsg h4')->text());

        $response = $this->confirmDeletion(self::BETA);

        self::assertSame(302, $response->getStatusCode(), self::serverLog());
        self::assertNull(self::mimetypeRow('icmsb'));
        self::assertSame([], self::useExtensionGroups(self::BETA));
    }

    public function testUnknownMimetypeIsNotSelected(): void
    {
        $response = $this->asWebmaster()->request('GET', self::PAGE . '&op=del&mimetypeid=999999');

        self::assertSame(302, $response->getStatusCode(), self::serverLog());
        $next = $this->request('GET', (string) $response->getHeader('Location'));
        self::assertStringContainsString('No object selected.', $next->getContent());
    }

    public function testAnonymousVisitorsAreSentToTheLogin(): void
    {
        $response = $this->request('GET', self::PAGE);

        self::assertSame(302, $response->getStatusCode(), self::serverLog());
        self::assertStringContainsString('user.php', (string) $response->getHeader('Location'));
    }

    private function page(string $path): Crawler
    {
        $crawler = $this->browser->request('GET', static::url($path));
        self::assertSame(200, $this->browser->getInternalResponse()->getStatusCode(), self::serverLog());

        return $crawler;
    }

    /**
     * The values of one column of the list, in order.
     *
     * @return list<string>
     */
    private function cells(Crawler $page, int $column): array
    {
        return $page->filter('table.ipf-list tr[data-id]')->each(static fn (Crawler $row): string => trim($row->filter('td')->eq($column)->text()));
    }
}
