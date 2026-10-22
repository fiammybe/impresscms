<?php

declare(strict_types=1);

namespace App\Tests\Http\Capability;

use App\Tests\Support\CapabilityHttpTestCase;
use App\Tests\Support\CapabilitySite;
use App\Tests\Support\TestSite;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Taxonomy on the unmodified news module, and the provider UI that comes as
 * ordinary module features: admin pages, an endpoint and blocks (design D12, D13).
 */
#[Group('spec:capability-ipf-integration')]
final class ProviderFeaturesTest extends CapabilityHttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        CapabilitySite::select('tagging', 'advancedtags');
        CapabilitySite::select('taxonomy', 'taxonomy');
        CapabilitySite::assign(['news/article' => ['tagging' => true, 'taxonomy' => true]]);
        $this->loginAsWebmaster();
    }

    public function testArticlesAreClassifiedThroughTheLegacyForm(): void
    {
        [$vocabularyId, $europe, $belgium] = self::regions();

        $form = $this->articleForm(0);
        $field = "capability[taxonomy][terms][{$vocabularyId}]";
        self::assertTrue($form->has($field), 'the taxonomy section has a select per vocabulary');

        $articleId = $this->createArticle('Classified article', [$field => (string) $belgium]);

        $relations = TestSite::connection()->query('SELECT term_id FROM ' . TestSite::table('taxonomy_relation') . " WHERE module = 'news' AND item = 'article' AND object_id = {$articleId}")->fetchAll(\PDO::FETCH_COLUMN);
        self::assertSame([(string) $belgium], array_map('strval', $relations));

        $breadcrumb = $this->page("/modules/news/article.php?article_id={$articleId}")->filterXPath('//span[@class="taxonomy-breadcrumb"]');
        self::assertSame('Europe › Belgium', $breadcrumb->text(), 'the single view shows the classification');

        $table = $this->page("/modules/news/admin/article.php?filtersel=capability_taxonomy&filtersel2={$europe}")->html();
        self::assertStringContainsString('Classified article', $table, 'the term filter includes the descendants');
    }

    /**
     * @scenario Tag cloud block
     */
    public function testTheTagCloudBlockUsesTheContractOnly(): void
    {
        $this->createArticle('Clouded article', ['capability[tagging][tags]' => 'cloudy, sunny']);

        $block = $this->blockOutput('news/article');

        self::assertSame('news/article', $block['objectType']);
        self::assertContains('cloudy', array_column($block['tags'], 'slug'));
        self::assertSame([], $this->blockOutput('news/category')['tags'], 'counts per object type');
    }

    public function testTheProviderAdministrationAndEndpointWork(): void
    {
        $this->createArticle('Endpoint article', ['capability[tagging][tags]' => 'endpoint']);

        $admin = $this->page('/modules/advancedtags/admin/tags.php');
        self::assertStringContainsString('endpoint', $admin->filterXPath('//table[@id="advancedtags-admin"]')->text());

        $response = $this->request('GET', '/modules/advancedtags/ajax/suggest.php?q=endp');
        self::assertSame(200, $response->getStatusCode(), self::serverLog());
        self::assertSame(['endpoint'], json_decode($response->getContent(), true));

        $tagPage = new Crawler($this->request('GET', '/modules/advancedtags/tag.php?slug=endpoint')->getContent());
        self::assertStringContainsString('Endpoint article', $tagPage->filterXPath('//ul[@id="tagged-objects"]')->text(), 'the provider resolves the tagged objects');
    }

    /**
     * The block function, run in a legacy boot of its own.
     *
     * @return array{tags: list<array{slug: string, count: int}>, objectType: string}
     */
    private function blockOutput(string $objectType): array
    {
        $process = new \Symfony\Component\Process\Process(
            [PHP_BINARY, '-r', sprintf(
                '$_SERVER += ["HTTP_HOST" => "127.0.0.1", "REQUEST_URI" => "/", "SCRIPT_NAME" => "/index.php", "REMOTE_ADDR" => "127.0.0.1", "REQUEST_METHOD" => "GET"]; require %s; require_once %s; echo json_encode(b_advancedtags_cloud_show([%s, "20"]));',
                var_export(TestSite::mainfile(), true),
                var_export(CapabilitySite::moduleDir('advancedtags') . '/blocks/cloud.php', true),
                var_export($objectType, true),
            )],
            TestSite::repoRoot(),
        );
        $process->mustRun();

        return json_decode((string) preg_replace('/^[^{]*/', '', $process->getOutput()), true, 512, JSON_THROW_ON_ERROR);
    }

    /** @return array{0: int, 1: int, 2: int} vocabulary, Europe, Belgium */
    private static function regions(): array
    {
        $pdo = TestSite::connection();
        $pdo->exec('INSERT INTO ' . TestSite::table('taxonomy_vocabulary') . " (label, multiple, object_types) VALUES ('Region', 0, 'news/article')");
        $vocabularyId = (int) $pdo->lastInsertId();
        $term = static function (string $label, int $parentId, string $parentPath) use ($pdo, $vocabularyId): array {
            $pdo->prepare('INSERT INTO ' . TestSite::table('taxonomy_term') . ' (vocabulary_id, parent_id, label, slug, weight, path) VALUES (?, ?, ?, ?, 0, ?)')
                ->execute([$vocabularyId, $parentId, $label, strtolower($label), '']);
            $id = (int) $pdo->lastInsertId();
            $path = $parentPath . str_pad((string) $id, 6, '0', STR_PAD_LEFT) . '/';
            $pdo->prepare('UPDATE ' . TestSite::table('taxonomy_term') . ' SET path = ? WHERE term_id = ?')->execute([$path, $id]);

            return [$id, $path];
        };
        [$europe, $europePath] = $term('Europe', 0, '/');
        [$belgium] = $term('Belgium', $europe, $europePath);

        return [$vocabularyId, $europe, $belgium];
    }
}
