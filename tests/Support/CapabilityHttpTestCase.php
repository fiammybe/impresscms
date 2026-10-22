<?php

declare(strict_types=1);

namespace App\Tests\Support;

use Symfony\Component\BrowserKit\Response;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Http tests against the test site with the capability fixture modules
 * installed: news (consumer), smarttags and advancedtags (tagging), taxonomy,
 * knowledgemanager (tagging and taxonomy). The modules are installed through
 * the module administration after the snapshot restore, and removed from
 * htdocs/modules after the class.
 */
abstract class CapabilityHttpTestCase extends HttpTestCase
{
    public static function setUpBeforeClass(): void
    {
        CapabilitySite::copyModules();
        parent::setUpBeforeClass();
        CapabilitySite::install(...CapabilitySite::MODULES);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        CapabilitySite::removeModules();
    }

    /**
     * Create an article through the news admin form, with optional capability
     * fields, and return its id.
     *
     * @param array<string, mixed> $fields form fields to set, by name
     */
    protected function createArticle(string $title, array $fields = []): int
    {
        $form = $this->articleForm(0);
        $form['title'] = $title;

        foreach ($fields as $name => $value) {
            $form[$name] = $value;
        }

        $response = $this->submit($form);
        self::assertSame(302, $response->getStatusCode(), 'the controller redirects after storing' . self::serverLog());

        return (int) TestSite::connection()
            ->query('SELECT MAX(article_id) FROM ' . TestSite::table('news_article'))
            ->fetchColumn();
    }

    protected function articleForm(int $articleId): \Symfony\Component\DomCrawler\Form
    {
        $crawler = $this->page('/modules/news/admin/article.php?op=mod' . ($articleId > 0 ? "&article_id={$articleId}" : ''));

        return $crawler->filterXPath('//form[@id="addarticle"]')->form();
    }

    protected function submit(\Symfony\Component\DomCrawler\Form $form): Response
    {
        $this->browser->submit($form, [], ['HTTP_REFERER' => $form->getUri()]);

        return $this->browser->getInternalResponse();
    }

    protected function page(string $path): Crawler
    {
        $response = $this->request('GET', $path);
        self::assertSame(200, $response->getStatusCode(), "GET {$path}" . self::serverLog());

        return new Crawler($response->getContent(), static::url($path));
    }

    /**
     * The tags a provider stores for an article, read from its own tables.
     *
     * @return list<string>
     */
    protected static function storedTags(string $provider, int $articleId): array
    {
        [$tags, $links] = match ($provider) {
            'smarttags' => ['smarttags_tags', 'smarttags_links'],
            'advancedtags' => ['advancedtags_tag', 'advancedtags_link'],
        };
        $statement = TestSite::connection()->prepare(
            'SELECT t.label FROM ' . TestSite::table($links) . ' l JOIN ' . TestSite::table($tags) . ' t ON t.tag_id = l.tag_id'
            . " WHERE l.module = 'news' AND l.item = 'article' AND l.object_id = ? ORDER BY t.label"
        );
        $statement->execute([$articleId]);

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }
}
