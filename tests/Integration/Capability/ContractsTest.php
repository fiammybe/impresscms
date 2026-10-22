<?php

declare(strict_types=1);

namespace App\Tests\Integration\Capability;

use App\Tests\Support\CapabilityLegacyTestCase;
use App\Tests\Support\CapabilitySite;
use App\Tests\Support\TestSite;
use Icms\Capability\Contract\Tagging\CapabilityInterface as Tagging;
use Icms\Capability\Contract\Tagging\Tag;
use Icms\Capability\Contract\Taxonomy\CapabilityInterface as Taxonomy;
use Icms\Capability\ObjectRef;
use Icms\Capability\Registry;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Every reference provider honours its contract (design D12, D13), and object
 * references resolve through the IPF object cache (design D4).
 */
#[RunTestsInSeparateProcesses]
#[Group('spec:capability-contracts')]
final class ContractsTest extends CapabilityLegacyTestCase
{
    /** @return array<string, array{0: string}> */
    public static function taggingProviders(): array
    {
        return ['smarttags' => ['smarttags'], 'advancedtags' => ['advancedtags'], 'knowledgemanager' => ['knowledgemanager']];
    }

    /** @return array<string, array{0: string}> */
    public static function taxonomyProviders(): array
    {
        return ['taxonomy' => ['taxonomy'], 'knowledgemanager' => ['knowledgemanager']];
    }

    /**
     * @scenario Setting tags
     */
    #[DataProvider('taggingProviders')]
    public function testSettingTagsReplacesThem(string $provider): void
    {
        $tagging = self::tagging($provider);
        $ref = ObjectRef::fromEntity(self::article("Set tags {$provider}"));

        $tagging->setTags($ref, ['php', 'cms']);
        self::assertSame(['cms', 'php'], self::labels($tagging->getTags($ref)));

        $tagging->setTags($ref, ['cms', 'ipf']);
        self::assertSame(['cms', 'ipf'], self::labels($tagging->getTags($ref)));

        $tagging->addTags($ref, ['php']);
        $tagging->removeTags($ref, ['cms']);
        self::assertSame(['ipf', 'php'], self::labels($tagging->getTags($ref)));
    }

    /**
     * @scenario Finding objects by tag
     */
    #[DataProvider('taggingProviders')]
    public function testObjectsAreFoundByTagInAStableOrder(string $provider): void
    {
        $tagging = self::tagging($provider);
        $slug = "find-{$provider}";
        $first = ObjectRef::fromEntity(self::article('First'));
        $untagged = ObjectRef::fromEntity(self::article('Untagged'));
        $second = ObjectRef::fromEntity(self::article('Second'));
        $tagging->setTags($first, [$slug]);
        $tagging->setTags($second, [$slug, 'other']);

        self::assertSame([$first->toString(), $second->toString()], self::strings($tagging->findObjects($slug, 'news/article')));
        self::assertSame([$first->toString()], self::strings($tagging->findObjects($slug, 'news/article', 1)));
        self::assertSame([$second->toString()], self::strings($tagging->findObjects($slug, 'news/article', 1, 1)));
        self::assertSame([], self::strings($tagging->findObjects($slug, 'news/category')));
        self::assertNotContains($untagged->toString(), self::strings($tagging->findObjects($slug)));
    }

    /**
     * @scenario Autocomplete
     */
    #[DataProvider('taggingProviders')]
    public function testSuggestionsStartWithThePrefixIgnoringCase(string $provider): void
    {
        $tagging = self::tagging($provider);
        $ref = ObjectRef::fromEntity(self::article('Suggest'));
        // labels of their own: the tags of the other tests are in the same tables
        $tagging->setTags($ref, ['Zqphunit', 'zqphrase', 'zqpython', 'graph']);

        $labels = self::labels($tagging->suggest('zqph'));

        self::assertSame(['zqphrase', 'Zqphunit'], $labels);
        self::assertCount(1, $tagging->suggest('ZQPH', 1));
    }

    /**
     * @scenario Tags for a list of articles
     */
    #[DataProvider('taggingProviders')]
    public function testTagsOfManyObjectsCostAFixedNumberOfQueries(string $provider): void
    {
        $tagging = self::tagging($provider);
        $refs = [];

        for ($i = 0; $i < 20; $i++) {
            $refs[] = $ref = ObjectRef::fromEntity(self::article("Batch {$i}"));

            if ($i % 2 === 0) {
                $tagging->setTags($ref, ["batch-{$i}"]);
            }
        }

        $tags = [];
        $queries = $this->queriesOf(static function () use ($tagging, $refs, &$tags): void {
            $tags = $tagging->getTagsForObjects($refs);
        });

        self::assertSame(array_map(static fn (ObjectRef $ref): string => $ref->toString(), $refs), array_keys($tags));
        self::assertSame(['batch-0'], self::labels($tags[$refs[0]->toString()]));
        self::assertSame([], $tags[$refs[1]->toString()], 'objects without tags map to an empty list');
        self::assertLessThanOrEqual(3, count($queries), implode("\n", $queries));
    }

    /**
     * @scenario Removing an untagged object
     */
    #[DataProvider('taggingProviders')]
    public function testClearingAnUntaggedObjectSucceeds(string $provider): void
    {
        $tagging = self::tagging($provider);
        $ref = ObjectRef::fromEntity(self::article('Never tagged'));

        $tagging->clear($ref);

        self::assertSame([], $tagging->getTags($ref));
    }

    /**
     * @scenario Hierarchical lookup
     */
    #[DataProvider('taxonomyProviders')]
    public function testDescendantTermsAreIncludedOnRequest(string $provider): void
    {
        $taxonomy = self::taxonomy($provider);
        [$europe, $belgium] = self::regions($provider);
        $ref = ObjectRef::fromEntity(self::article('Classified'));

        $taxonomy->classify($ref, [(string) $belgium]);

        self::assertContains($ref->toString(), self::strings($taxonomy->findObjects((string) $europe, true, 'news/article')));
        self::assertNotContains($ref->toString(), self::strings($taxonomy->findObjects((string) $europe, false, 'news/article')));
        self::assertSame([(string) $belgium], array_map(static fn ($term): string => $term->id, $taxonomy->termsOf($ref)));
        self::assertSame((string) $europe, $taxonomy->termsOf($ref)[0]->parentId);
    }

    /**
     * @scenario Single-term vocabulary
     */
    public function testASingleTermVocabularyRejectsTwoTerms(): void
    {
        $taxonomy = self::taxonomy('taxonomy');
        [$europe, $belgium] = self::regions('taxonomy');
        $ref = ObjectRef::fromEntity(self::article('Twice classified'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"Region"');

        $taxonomy->classify($ref, [(string) $europe, (string) $belgium]);
    }

    /**
     * @scenario Resolving a page of tagged articles
     */
    public function testReferencesResolveWithOneQueryPerType(): void
    {
        $refs = [];

        for ($i = 0; $i < 20; $i++) {
            $refs[] = ObjectRef::fromEntity(self::article("Resolved {$i}"));
        }

        $objects = [];
        $queries = $this->queriesOf(static function () use ($refs, &$objects): void {
            $objects = ObjectRef::resolveMany($refs);
        });

        self::assertCount(20, $objects);
        self::assertSame('Resolved 7', $objects[$refs[7]->toString()]->getVar('title', 'n'));
        self::assertCount(1, array_filter($queries, static fn (string $sql): bool => str_contains($sql, 'news_article')), implode("\n", $queries));
        self::assertSame([], $this->queriesOf(static fn () => ObjectRef::resolveMany($refs)), 'objects loaded in the request are not queried again');
        self::assertSame('Resolved 3', $refs[3]->resolve()?->getVar('title', 'n'));
    }

    /**
     * @scenario Consumer module uninstalled
     */
    public function testReferencesOfAModuleThatIsNotInstalledResolveToNothing(): void
    {
        self::assertSame([], ObjectRef::resolveMany([new ObjectRef('nolongerinstalled', 'article', 42)]));
    }

    /**
     * @scenario Deleted object
     */
    public function testReferencesOfADeletedObjectResolveToNothing(): void
    {
        $ref = ObjectRef::fromEntity(self::article('Deleted with SQL'));
        TestSite::connection()->exec('DELETE FROM ' . TestSite::table('news_article') . " WHERE article_id = {$ref->id}");

        self::assertNull($ref->resolve());
    }

    private static function tagging(string $provider): Tagging
    {
        CapabilitySite::select('tagging', $provider);
        self::reload();
        $tagging = Registry::capability('tagging');
        self::assertInstanceOf(Tagging::class, $tagging);
        self::assertSame($provider, $tagging->providerModule());

        return $tagging;
    }

    private static function taxonomy(string $provider): Taxonomy
    {
        CapabilitySite::select('taxonomy', $provider);
        self::reload();
        $taxonomy = Registry::capability('taxonomy');
        self::assertInstanceOf(Taxonomy::class, $taxonomy);

        return $taxonomy;
    }

    /**
     * The terms Europe and Europe > Belgium, in the provider's own way.
     *
     * @return array{0: int, 1: int}
     */
    private static function regions(string $provider): array
    {
        if ($provider === 'knowledgemanager') {
            $keywords = icms_getModuleHandler('keyword', 'knowledgemanager');
            $europe = $keywords->idFor('topic', 'Europe');

            return [$europe, $keywords->idFor('topic', 'Belgium', $europe)];
        }

        $vocabularies = icms_getModuleHandler('vocabulary', 'taxonomy');
        $vocabulary = $vocabularies->create();
        $vocabulary->setVar('label', 'Region');
        $vocabulary->setVar('multiple', 0);
        $vocabularies->insert($vocabulary, true);
        $terms = icms_getModuleHandler('term', 'taxonomy');
        $europe = $terms->add((int) $vocabulary->getVar('vocabulary_id'), 'Europe');

        return [$europe, $terms->add((int) $vocabulary->getVar('vocabulary_id'), 'Belgium', $europe)];
    }

    /**
     * @param list<Tag> $tags
     * @return list<string>
     */
    private static function labels(array $tags): array
    {
        $labels = array_map(static fn (Tag $tag): string => $tag->label, $tags);
        sort($labels, SORT_STRING | SORT_FLAG_CASE);

        return $labels;
    }

    /**
     * @param list<ObjectRef> $refs
     * @return list<string>
     */
    private static function strings(array $refs): array
    {
        return array_map(static fn (ObjectRef $ref): string => $ref->toString(), $refs);
    }
}
