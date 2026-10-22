<?php

declare(strict_types=1);

namespace App\Tests\Http\Capability;

use App\Tests\Support\CapabilityHttpTestCase;
use App\Tests\Support\CapabilitySite;
use App\Tests\Support\TestSite;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tagging reaches the unmodified news module end to end: its legacy IPF form,
 * its save and delete, the generic single view, its admin table and a theme
 * template override (design D11, D12).
 */
#[Group('spec:capability-ipf-integration')]
final class TaggingEndToEndTest extends CapabilityHttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        CapabilitySite::select('tagging', 'smarttags');
        CapabilitySite::assign(['news/article' => ['tagging' => true]]);
        $this->loginAsWebmaster();
    }

    /**
     * @scenario Tag field on the article form
     * @scenario Creating a tagged article
     */
    public function testAnArticleIsTaggedThroughTheLegacyForm(): void
    {
        $form = $this->articleForm(0);
        self::assertTrue($form->has('capability[tagging][tags]'), 'the form has the tagging section');
        self::assertTrue($form->has('capability_REQUEST'), 'the capability fields carry their own token');
        self::assertTrue($form->has('title'), 'the article\'s own fields are unchanged');

        $articleId = $this->createArticle('Tagged article', ['capability[tagging][tags]' => 'php, cms']);

        self::assertSame(['cms', 'php'], self::storedTags('smarttags', $articleId));
        self::assertSame('php, cms', $this->articleForm($articleId)->get('capability[tagging][tags]')->getValue(), 'the field is prefilled');
    }

    /**
     * @scenario Name collision avoided
     */
    public function testTheArticlesOwnTagsVariableIsKeptApart(): void
    {
        $articleId = $this->createArticle('Own tags', ['tags' => 'my own value', 'capability[tagging][tags]' => 'alpha']);

        $own = TestSite::connection()->query('SELECT tags FROM ' . TestSite::table('news_article') . " WHERE article_id = {$articleId}")->fetchColumn();
        self::assertSame('my own value', $own);
        self::assertSame(['alpha'], self::storedTags('smarttags', $articleId));
    }

    public function testCapabilityFieldsWithoutTheirTokenAreIgnored(): void
    {
        $form = $this->articleForm(0);
        $form['title'] = 'No token';
        $form['capability[tagging][tags]'] = 'forged';
        $values = $form->getPhpValues();
        unset($values['capability_REQUEST']);

        $this->request('POST', '/modules/news/admin/article.php', $values, ['HTTP_REFERER' => $form->getUri()]);
        $articleId = (int) TestSite::connection()->query('SELECT MAX(article_id) FROM ' . TestSite::table('news_article'))->fetchColumn();

        self::assertSame('No token', TestSite::connection()->query('SELECT title FROM ' . TestSite::table('news_article') . " WHERE article_id = {$articleId}")->fetchColumn());
        self::assertSame([], self::storedTags('smarttags', $articleId), 'the article is stored, its capability fields are not');
    }

    /**
     * @scenario Tags in the single view
     */
    public function testTheSingleViewShowsTheTagsAsLinks(): void
    {
        $articleId = $this->createArticle('Viewed article', ['capability[tagging][tags]' => 'php']);

        $links = $this->page("/modules/news/article.php?article_id={$articleId}")->filterXPath('//*[@id="article-singleview"]//a[contains(@class, "smarttags-tag")]');

        self::assertSame(['php'], $links->each(static fn ($link): string => $link->text()));
        self::assertStringContainsString('/modules/smarttags/tag.php?slug=php', (string) $links->attr('href'));
    }

    /**
     * @scenario Filtering articles by tag
     */
    public function testTheAdminTableFiltersByTag(): void
    {
        $this->createArticle('Filter hit', ['capability[tagging][tags]' => 'filterme']);
        $this->createArticle('Filter miss', ['capability[tagging][tags]' => 'other']);

        $html = $this->page('/modules/news/admin/article.php?filtersel=capability_tagging&filtersel2=filterme')->html();

        self::assertStringContainsString('Filter hit', $html);
        self::assertStringNotContainsString('Filter miss', $html);
        self::assertStringContainsString('filterme (1)', $html, 'the filter offers the tags with their counts');
    }

    /**
     * @scenario Deleting a tagged article
     */
    public function testDeletingAnArticleClearsItsTags(): void
    {
        $articleId = $this->createArticle('Deleted article', ['capability[tagging][tags]' => 'gone']);
        self::assertSame(['gone'], self::storedTags('smarttags', $articleId));

        $response = $this->request('POST', '/modules/news/admin/article.php', [
            'op' => 'del',
            'article_id' => $articleId,
            'confirm' => 1,
            'redirect_page' => static::url('/modules/news/admin/article.php'),
        ], ['HTTP_REFERER' => static::url('/modules/news/admin/article.php')]);

        self::assertSame(302, $response->getStatusCode(), self::serverLog());
        self::assertSame(0, (int) TestSite::connection()->query('SELECT COUNT(*) FROM ' . TestSite::table('news_article') . " WHERE article_id = {$articleId}")->fetchColumn());
        self::assertSame([], self::storedTags('smarttags', $articleId));
    }

    /**
     * @scenario Unassigned object type
     */
    public function testAnUnassignedObjectTypeGetsNoCapabilityFields(): void
    {
        CapabilitySite::assign(['news/article' => ['tagging' => false]]);

        $form = $this->articleForm(0);

        self::assertFalse($form->has('capability[tagging][tags]'));
        self::assertFalse($form->has('capability_REQUEST'));
    }
}
