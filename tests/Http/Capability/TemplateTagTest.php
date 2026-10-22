<?php

declare(strict_types=1);

namespace App\Tests\Http\Capability;

use App\Tests\Support\CapabilityHttpTestCase;
use App\Tests\Support\CapabilitySite;
use App\Tests\Support\TestSite;
use PHPUnit\Framework\Attributes\Group;

/**
 * The <{icms_capability}> Smarty tag in a theme template override of the
 * unmodified news module (design D11). A class of its own: the server must
 * not have compiled the module's template before the override exists.
 */
#[Group('spec:capability-ipf-integration')]
final class TemplateTagTest extends CapabilityHttpTestCase
{
    private const OVERRIDE = '/themes/iTheme/modules/news/news_article.html';

    protected function setUp(): void
    {
        parent::setUp();
        CapabilitySite::select('tagging', 'smarttags');
        CapabilitySite::assign(['news/article' => ['tagging' => true]]);
        $this->loginAsWebmaster();
    }

    protected function tearDown(): void
    {
        @unlink(TestSite::legacyRoot() . self::OVERRIDE);
        @rmdir(dirname(TestSite::legacyRoot() . self::OVERRIDE));
        TestSite::clearLegacyCaches();
        parent::tearDown();
    }

    /**
     * @scenario Tag list in a custom template
     * @scenario Capability disabled
     */
    public function testATemplateOverrideRendersTheCapabilityTag(): void
    {
        $articleId = $this->createArticle('Template article', ['capability[tagging][tags]' => 'templated']);
        $override = TestSite::legacyRoot() . self::OVERRIDE;
        @mkdir(dirname($override), 0777, true);
        file_put_contents($override, '<div id="article-tags"><{icms_capability name="tagging" object=$news_article_object variant="list"}></div>');
        TestSite::clearLegacyCaches();

        $page = $this->page("/modules/news/article.php?article_id={$articleId}");
        self::assertSame(
            ['templated'],
            $page->filterXPath('//*[@id="article-tags"]/ul[contains(@class, "smarttags-list")]/li')->each(static fn ($li): string => $li->text()),
            $page->html() . self::serverLog(),
        );

        CapabilitySite::assign(['news/article' => ['tagging' => false]]);

        self::assertSame('', trim($this->page("/modules/news/article.php?article_id={$articleId}")->filterXPath('//*[@id="article-tags"]')->html()));
    }
}
