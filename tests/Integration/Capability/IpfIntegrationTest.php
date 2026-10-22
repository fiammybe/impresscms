<?php

declare(strict_types=1);

namespace App\Tests\Integration\Capability;

use App\Tests\Support\CapabilityLegacyTestCase;
use App\Tests\Support\CapabilitySite;
use App\Tests\Support\TestSite;
use Icms\Capability\ObjectRef;
use Icms\Capability\Registry;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * The IPF save hook and the admin table in-process (design D11).
 */
#[RunTestsInSeparateProcesses]
#[Group('spec:capability-ipf-integration')]
final class IpfIntegrationTest extends CapabilityLegacyTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        CapabilitySite::select('tagging', 'smarttags');
        CapabilitySite::select('taxonomy', 'taxonomy');
        CapabilitySite::assign(['news/article' => ['tagging' => true]]);
        self::reload();
    }

    /**
     * @scenario Programmatic save
     */
    public function testAProgrammaticSaveLeavesProviderDataUntouched(): void
    {
        $article = self::article('Programmatic');
        $ref = ObjectRef::fromEntity($article);
        Registry::capability('tagging')->setTags($ref, ['kept']);

        $article->setVar('title', 'Programmatic, saved again');
        self::assertTrue(icms_getModuleHandler('article', 'news')->insert($article, true));

        self::assertSame(['kept'], self::labels($ref));
    }

    /**
     * @scenario Object save fails
     */
    public function testAFailedObjectSaveCallsNoProvider(): void
    {
        self::submitCapabilityFields(['tagging' => ['tags' => 'never']]);
        $handler = icms_getModuleHandler('article', 'news');
        $article = $handler->create();
        // longer than the column: the database rejects the row
        $article->setVar('title', str_repeat('x', 300));

        self::assertFalse($handler->insert($article, true), 'the row is not stored');
        self::assertSame(0, (int) TestSite::connection()->query('SELECT COUNT(*) FROM ' . TestSite::table('smarttags_tags') . " WHERE label = 'never'")->fetchColumn());
    }

    public function testASubmissionIsStoredOncePerObjectAndRequest(): void
    {
        self::submitCapabilityFields(['tagging' => ['tags' => 'first']]);
        $article = self::article('Saved twice');

        $_POST['capability'] = ['tagging' => ['tags' => 'second']];
        $article->setVar('title', 'Saved twice, again');
        icms_getModuleHandler('article', 'news')->insert($article, true);

        self::assertSame(['first'], self::labels(ObjectRef::fromEntity($article)));
    }

    public function testProviderErrorsAreReportedOnTheObject(): void
    {
        CapabilitySite::assign(['news/article' => ['taxonomy' => true]]);
        self::reload();
        $vocabularies = icms_getModuleHandler('vocabulary', 'taxonomy');
        $vocabulary = $vocabularies->create();
        $vocabulary->setVar('label', 'Single');
        $vocabularies->insert($vocabulary, true);
        $vocabularyId = (int) $vocabulary->getVar('vocabulary_id');
        $terms = icms_getModuleHandler('term', 'taxonomy');
        $one = $terms->add($vocabularyId, 'One');
        $two = $terms->add($vocabularyId, 'Two');
        self::submitCapabilityFields(['taxonomy' => ['terms' => [$vocabularyId => [$one, $two]]]]);

        $handler = icms_getModuleHandler('article', 'news');
        $article = $handler->create();
        $article->setVar('title', 'Two terms');

        self::assertTrue($handler->insert($article, true), 'the object stays stored');
        self::assertStringContainsString('Taxonomy: The vocabulary "Single" allows one term per object.', implode("\n", $article->getErrors()));
        self::assertSame(['Taxonomy: The vocabulary "Single" allows one term per object.'], \Icms\Capability\Integration::instance()->errorsFor($article), 'for the controller\'s message');
    }

    /**
     * @scenario Tags for a list of articles
     */
    #[Group('spec:capability-contracts')]
    public function testATablePageCostsAFixedNumberOfProviderQueries(): void
    {
        for ($i = 0; $i < 20; $i++) {
            Registry::capability('tagging')->setTags(ObjectRef::fromEntity(self::article("Table {$i}")), ["table-{$i}"]);
        }
        $_GET = ['limitsel' => 20];
        $table = new \icms_ipf_view_Table(icms_getModuleHandler('article', 'news'));
        $table->addColumn(new \icms_ipf_view_Column('title'));

        $html = '';
        $queries = $this->queriesOf(static function () use ($table, &$html): void {
            // the page navigation echoes its stylesheet
            ob_start();
            $html = $table->fetch();
            ob_end_clean();
        });

        self::assertStringContainsString('Table 19', $html);
        $providerQueries = array_filter($queries, static fn (string $sql): bool => str_contains($sql, 'smarttags_'));
        self::assertLessThanOrEqual(4, count($providerQueries), implode("\n", $providerQueries));
    }

    /**
     * Post capability fields with a valid capability token, as the IPF form does.
     *
     * @param array<string, array<string, mixed>> $fields
     */
    private static function submitCapabilityFields(array $fields): void
    {
        $_POST['capability'] = $fields;
        $_POST['capability_REQUEST'] = $_REQUEST['capability_REQUEST'] = \icms::$security->createToken(0, 'capability');
    }

    /** @return list<string> */
    private static function labels(ObjectRef $ref): array
    {
        return array_map(static fn ($tag): string => $tag->label, Registry::capability('tagging')->getTags($ref));
    }
}
