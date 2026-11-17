<?php

declare(strict_types=1);

namespace App\Tests\Integration\Ipf;

use App\Ipf\IpfRepository;
use App\Ipf\Metadata\DefinitionRegistry;
use App\Ipf\Metadata\EntityDefinition;
use App\Ipf\Query\InvalidListQueryException;
use App\Ipf\Query\ListQuery;
use App\Ipf\Query\ListQueryResolver;
use App\Ipf\Table\LegacyTableRenderer;
use App\Ipf\Table\TableConfig;
use App\Ipf\Table\TableFactory;
use App\Ipf\Table\TwigTableRenderer;
use App\Legacy\LegacyRuntime;
use App\Tests\Support\LegacyWebTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;

/**
 * The list pipeline against the legacy IPF table, on the real mimetype
 * object (design D3, D4, D5).
 */
#[RunTestsInSeparateProcesses]
#[Group('spec:ipf-lists')]
final class IpfListsTest extends LegacyWebTestCase
{
    private const PATH = '/modules/system/admin.php';
    private const SEARCH = 'quicksearch_SystemMimetype';

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        static::getContainer()->get(LegacyRuntime::class)->bootTo('online');
        icms_loadLanguageFile('system', 'common');
    }

    /**
     * @scenario Legacy parameter names
     */
    public function testTableParameterNamesSortTheList(): void
    {
        $parameters = ['mimetype_sortsel' => 'extension', 'mimetype_ordersel' => 'DESC'];
        $query = self::tableQuery($parameters);

        self::assertSame('extension', $query->sort);
        self::assertSame(ListQuery::DESC, $query->order);

        $extensions = array_map(
            static fn (object $mimetype): string => (string) $mimetype->getVar('extension', 'n'),
            self::repository()->list(self::definition(), $query)->items,
        );
        $sorted = $extensions;
        rsort($sorted, SORT_STRING | SORT_FLAG_CASE);

        self::assertNotEmpty($extensions);
        self::assertSame($sorted, $extensions);
        self::assertSame(self::legacyIds($parameters), self::ids($query));
    }

    /**
     * @scenario Unknown sort field
     */
    public function testUnknownFieldsAreRejected(): void
    {
        foreach ([
            ['mimetype_sortsel' => 'no_such_field'],
            ['mimetype_sortsel' => 'name; DROP TABLE x'],
            ['mimetype_ordersel' => 'sideways'],
            ['filtersel' => 'no_such_field', 'filtersel2' => '1'],
        ] as $parameters) {
            try {
                self::tableQuery($parameters);
                self::fail('Accepted ' . json_encode($parameters));
            } catch (InvalidListQueryException $e) {
                self::assertNotSame('', $e->getMessage());
            }
        }

        $this->expectException(InvalidListQueryException::class);
        self::resolver()->fromApiRequest(Request::create('/', 'GET', ['sort' => 'no_such_field']), self::definition());
    }

    /**
     * @scenario Excessive limit
     */
    public function testTheLimitIsCapped(): void
    {
        $query = self::resolver()->fromApiRequest(Request::create('/', 'GET', ['limit' => 100000]), self::definition());

        self::assertSame(ListQueryResolver::MAX_LIMIT, $query->limit);
        self::assertLessThanOrEqual(ListQueryResolver::MAX_LIMIT, count(self::repository()->list(self::definition(), $query)->items));

        $second = self::resolver()->fromApiRequest(Request::create('/', 'GET', ['limit' => 2, 'page' => 3]), self::definition());
        self::assertSame(4, $second->offset);
        self::assertSame(3, $second->page());
    }

    /**
     * @scenario Default list
     */
    public function testDefaultListIsThatOfTheLegacyTable(): void
    {
        $query = self::tableQuery([]);
        $result = self::repository()->list(self::definition(), $query);

        self::assertNotEmpty($result->items);
        self::assertSame(self::legacyIds([]), self::ids($query));
        self::assertSame((int) self::mimetypes()->getCount(), $result->total);

        $next = ['startmimetypeid' => 15];
        self::assertSame(self::legacyIds($next), self::ids(self::tableQuery($next)), 'the second page');
    }

    /**
     * @scenario Quick search
     */
    public function testQuickSearchFindsWhatTheLegacyTableFinds(): void
    {
        $parameters = [self::SEARCH => 'Fixture Alpha'];
        $query = self::tableQuery($parameters);
        $result = self::repository()->list(self::definition(), $query);
        $legacy = self::legacyIds($parameters);

        self::assertNotEmpty($legacy, 'the fixture mimetype is found');
        self::assertSame($legacy, self::ids($query));
        self::assertSame(count($legacy), $result->total);
    }

    /**
     * @scenario Mimetype admin list unchanged
     */
    public function testLegacyRendererGivesTheLegacyTable(): void
    {
        foreach ([[], ['mimetype_sortsel' => 'extension', 'mimetype_ordersel' => 'DESC'], [self::SEARCH => 'icms']] as $parameters) {
            $request = self::request($parameters);
            $legacy = self::legacyTable($parameters);
            $expected = self::quietly(static fn (): string => (string) $legacy->fetch());
            $renderer = static::getContainer()->get(LegacyTableRenderer::class);
            $table = self::table();
            if (isset($parameters['mimetype_sortsel'])) {
                $table->setDefaultSort($parameters['mimetype_sortsel'], $parameters['mimetype_ordersel']);
            }

            $html = self::quietly(static fn (): string => $renderer->render($table, $request));

            self::assertNotSame('', trim($expected));
            self::assertSame($expected, $html, json_encode($parameters));
        }
    }

    /**
     * @scenario Twig renderer shows the same rows
     */
    public function testTwigRendererShowsTheRowsOfTheLegacyList(): void
    {
        foreach ([[], ['mimetype_sortsel' => 'name', 'mimetype_ordersel' => 'DESC'], [self::SEARCH => 'icms']] as $parameters) {
            $html = static::getContainer()->get(TwigTableRenderer::class)->render(self::table(), self::request($parameters));
            $page = new Crawler($html);

            $ids = $page->filterXPath('//tbody/tr[@data-id]')->each(static fn (Crawler $row): int => (int) $row->attr('data-id'));
            self::assertSame(self::legacyIds($parameters), $ids, json_encode($parameters));

            $captions = $page->filterXPath('//thead//th[@data-field]')->each(static fn (Crawler $cell): string => trim($cell->text()));
            self::assertSame([_CO_ICMS_MIMETYPE_NAME, _CO_ICMS_MIMETYPE_EXTENSION, _CO_ICMS_MIMETYPE_TYPES], $captions);
        }

        $page = new Crawler(static::getContainer()->get(TwigTableRenderer::class)->render(self::table(), self::request([])));
        $first = self::mimetypes()->get(self::legacyIds([])[0]);
        $cells = $page->filterXPath('//tbody/tr[1]/td');

        self::assertSame((string) $first->getVar('name'), $cells->eq(0)->html());
        self::assertSame((string) $first->getVar('extension'), $cells->eq(1)->html());
        self::assertStringContainsString('op=mod', $cells->eq(3)->html(), 'the edit link of the legacy table');
        self::assertStringContainsString('mimetype_sortsel=name', (string) $page->filterXPath('//thead//th[@data-field="name"]/a')->attr('href'));
        self::assertStringContainsString('startmimetypeid=15', (string) $page->filterXPath('//a[@rel="next"]')->attr('href'));
        self::assertSame(self::SEARCH, $page->filterXPath('//input[@type="search"]')->attr('name'));
    }

    public function testDefaultTableComesFromTheDescription(): void
    {
        $table = static::getContainer()->get(TableFactory::class)->create(self::mimetypes());
        $fields = array_map(static fn (object $column): string => $column->field, $table->columns());

        // short stored fields other than the key; the quick search covers the text boxes
        self::assertSame(['extension', 'name'], $fields);
        self::assertSame(['extension', 'name'], $table->quickSearch());
        self::assertSame('SystemMimetype', $table->id());
    }

    /**
     * @param array<string, int|string> $parameters
     */
    private static function tableQuery(array $parameters): ListQuery
    {
        return self::resolver()->fromTableRequest(self::request($parameters), self::definition(), 'SystemMimetype', ['name', 'extension', 'types']);
    }

    /**
     * A request, mirrored into the superglobals the legacy table reads.
     *
     * @param array<string, int|string> $parameters
     */
    private static function request(array $parameters): Request
    {
        $_GET = $parameters + ['fct' => 'mimetype'];
        $_POST = [];
        $_COOKIE = [];
        $_SERVER['SCRIPT_NAME'] = self::PATH;

        return Request::create(self::PATH, 'GET', $_GET);
    }

    /**
     * The ids the legacy table shows for a request, in its order.
     *
     * @param array<string, int|string> $parameters
     *
     * @return list<int>
     */
    private static function legacyIds(array $parameters): array
    {
        self::request($parameters);
        $table = self::legacyTable($parameters);
        self::quietly(static fn (): string => (string) $table->fetch());

        return array_values(array_map(static fn (object $mimetype): int => (int) $mimetype->getVar('mimetypeid'), $table->getObjects()));
    }

    /**
     * The table of modules/system/admin/mimetype/main.php.
     *
     * @param array<string, int|string> $parameters
     */
    private static function legacyTable(array $parameters = []): \Icms\Ipf\View\Table
    {
        // the legacy table reads its sort options with filter_input(), which
        // sees no request in a CLI process: they go in as its defaults, and
        // the request itself is covered by the Http snapshots
        $sort = $parameters['mimetype_sortsel'] ?? null;
        unset($_GET['mimetype_sortsel'], $_GET['mimetype_ordersel']);

        $table = new \Icms\Ipf\View\Table(self::mimetypes());
        $table->addColumn(new \Icms\Ipf\View\Column('name', _GLOBAL_LEFT, 150));
        $table->addColumn(new \Icms\Ipf\View\Column('extension', _GLOBAL_LEFT, 150));
        $table->addColumn(new \Icms\Ipf\View\Column('types', _GLOBAL_LEFT));
        $table->addIntroButton('addmimetype', 'admin.php?fct=mimetype&amp;op=mod', _CO_ICMS_MIMETYPE_CREATE);
        $table->addQuickSearch(['name', 'extension', 'types']);
        if ($sort !== null) {
            $table->setDefaultSort($sort);
            $table->setDefaultOrder($parameters['mimetype_ordersel'] ?? 'ASC');
        }

        return $table;
    }

    /**
     * The legacy page navigation echoes its stylesheet link when no theme is
     * active; a page collects that in its output buffer.
     *
     * @param \Closure(): string $render
     */
    private static function quietly(\Closure $render): string
    {
        ob_start();
        try {
            return $render();
        } finally {
            ob_end_clean();
        }
    }

    private static function table(): TableConfig
    {
        return static::getContainer()->get(TableFactory::class)->create(self::mimetypes())
            ->clearColumns()
            ->addColumn('name', width: 150)
            ->addColumn('extension', width: 150)
            ->addColumn('types')
            ->addIntroButton('addmimetype', 'admin.php?fct=mimetype&amp;op=mod', _CO_ICMS_MIMETYPE_CREATE)
            ->setQuickSearch(['name', 'extension', 'types']);
    }

    /**
     * @return list<int>
     */
    private static function ids(ListQuery $query): array
    {
        return array_map(
            static fn (object $mimetype): int => (int) $mimetype->getVar('mimetypeid'),
            self::repository()->list(self::definition(), $query)->items,
        );
    }

    private static function definition(): EntityDefinition
    {
        return static::getContainer()->get(DefinitionRegistry::class)->get('system', 'mimetype');
    }

    private static function resolver(): ListQueryResolver
    {
        return static::getContainer()->get(ListQueryResolver::class);
    }

    private static function repository(): IpfRepository
    {
        return static::getContainer()->get(IpfRepository::class);
    }

    private static function mimetypes(): object
    {
        return static::getContainer()->get('icms.module_handlers')->get('system', 'mimetype');
    }
}
