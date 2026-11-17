<?php

declare(strict_types=1);

namespace App\Tests\Integration\Ipf;

use App\Ipf\Example\Mimetype;
use App\Ipf\IpfRepository;
use App\Ipf\Metadata\DefinitionRegistry;
use App\Ipf\Metadata\EntityDefinition;
use App\Ipf\Query\ListQuery;
use App\Ipf\Store\DoctrineStore;
use App\Ipf\Store\HandlerStore;
use App\Ipf\Table\TableFactory;
use App\Ipf\Table\TwigTableRenderer;
use App\Legacy\LegacyRuntime;
use App\Tests\Support\LegacyWebTestCase;
use App\Tests\Support\TestSite;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;

/**
 * One table, two declarations (design D7): the mimetype IPF object and the
 * ORM-mapped example class App\Ipf\Example\Mimetype give the same description
 * and the same lists.
 */
#[RunTestsInSeparateProcesses]
final class IpfOrmObjectsTest extends LegacyWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        static::getContainer()->get(LegacyRuntime::class)->bootTo('online');
    }

    /**
     * @scenario Same table, two declarations
     */
    #[Group('spec:ipf-metadata')]
    public function testOrmClassYieldsTheDescriptionOfTheIpfObject(): void
    {
        $ipf = self::definition('mimetype');
        $orm = self::definition('mimetype_orm');

        self::assertSame(EntityDefinition::SOURCE_ORM, $orm->source);
        self::assertSame(Mimetype::class, $orm->className);
        self::assertSame($ipf->table, $orm->table, 'with the site\'s table prefix');
        self::assertSame(TestSite::table('system_mimetype'), $orm->table);
        self::assertSame($ipf->keyField, $orm->keyField);
        self::assertSame($ipf->summaryField, $orm->summaryField);
        self::assertSame(array_keys($ipf->persistentFields()), array_keys($orm->persistentFields()));

        foreach ($ipf->fields as $name => $field) {
            self::assertSame($field->dataType, $orm->field($name)->dataType, $name);
            self::assertSame($field->caption, $orm->field($name)->caption, $name);
            self::assertSame($field->description, $orm->field($name)->description, $name);
            self::assertSame($field->required, $orm->field($name)->required, $name);
        }
    }

    /**
     * @scenario Same rows from both declarations
     */
    #[Group('spec:ipf-lists')]
    public function testBothStoresGiveTheSameLists(): void
    {
        $repository = static::getContainer()->get(IpfRepository::class);
        $ipf = self::definition('mimetype');
        $orm = self::definition('mimetype_orm');

        self::assertInstanceOf(HandlerStore::class, $repository->store($ipf));
        self::assertInstanceOf(DoctrineStore::class, $repository->store($orm));

        $search = ['name', 'extension', 'types'];
        foreach ([
            'default' => new ListQuery('mimetypeid'),
            'second page' => new ListQuery('mimetypeid', ListQuery::ASC, 15, 15),
            'sorted' => new ListQuery('extension', ListQuery::DESC, 0, 10),
            'search' => new ListQuery('name', ListQuery::ASC, 0, 15, 'icms', $search),
            'filter' => new ListQuery('mimetypeid', ListQuery::ASC, 0, 15, '', [], ['extension' => 'icmsb']),
            'no limit' => new ListQuery('name', ListQuery::DESC, 0, 0),
            'nothing' => new ListQuery('mimetypeid', ListQuery::ASC, 0, 15, 'no such mimetype anywhere', $search),
        ] as $case => $query) {
            $fromHandler = $repository->list($ipf, $query);
            $fromOrm = $repository->list($orm, $query);

            $ids = array_map(static fn (object $mimetype): int => (int) $mimetype->getVar('mimetypeid'), $fromHandler->items);
            self::assertSame($ids, array_map(static fn (Mimetype $mimetype): ?int => $mimetype->getMimetypeid(), $fromOrm->items), $case);
            self::assertSame($fromHandler->total, $fromOrm->total, $case);
            if ($case !== 'nothing') {
                self::assertNotEmpty($ids, $case);
            }
        }

        $id = TestSite::connection()->query('SELECT mimetypeid FROM ' . TestSite::table('system_mimetype') . " WHERE extension = 'icmsc'")->fetchColumn();
        $fromHandler = $repository->find($ipf, (int) $id);
        $fromOrm = $repository->find($orm, (int) $id);
        self::assertNotNull($fromHandler);
        self::assertInstanceOf(Mimetype::class, $fromOrm);
        foreach (array_keys($ipf->fields) as $field) {
            self::assertSame(
                $repository->store($ipf)->value($ipf, $fromHandler, $field),
                $repository->store($orm)->value($orm, $fromOrm, $field),
                $field,
            );
        }

        self::assertNull($repository->find($orm, 999999));
    }

    public function testOrmObjectGetsATwigList(): void
    {
        $table = static::getContainer()->get(TableFactory::class)->forObject('system', 'mimetype_orm');
        $html = static::getContainer()->get(TwigTableRenderer::class)
            ->render($table, Request::create('/list', 'GET', ['quicksearch_' . $table->id() => 'Fixture', 'mimetype_orm_sortsel' => 'name', 'mimetype_orm_ordersel' => 'DESC']));
        $page = new Crawler($html);

        self::assertSame(
            [_CO_ICMS_MIMETYPE_EXTENSION, _CO_ICMS_MIMETYPE_NAME],
            $page->filterXPath('//thead//th[@data-field]')->each(static fn (Crawler $cell): string => trim($cell->text())),
        );
        self::assertSame(
            ['ICMS Fixture Gamma', 'ICMS Fixture Beta', 'ICMS Fixture Alpha'],
            $page->filterXPath('//tbody/tr/td[2]')->each(static fn (Crawler $cell): string => $cell->text()),
        );
    }

    private static function definition(string $item): EntityDefinition
    {
        return static::getContainer()->get(DefinitionRegistry::class)->get('system', $item);
    }
}
