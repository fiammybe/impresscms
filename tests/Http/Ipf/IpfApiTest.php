<?php

declare(strict_types=1);

namespace App\Tests\Http\Ipf;

use App\Tests\Support\HttpTestCase;
use App\Tests\Support\TestSite;
use PHPUnit\Framework\Attributes\Group;

/**
 * The read-only API of IPF objects on the mimetype object of the system
 * module, for the identities of the test site (design D6).
 */
#[Group('spec:ipf-api')]
final class IpfApiTest extends HttpTestCase
{
    private const LIST = '/api/ipf/system/mimetype';

    /**
     * @scenario Webmaster reads mimetypes
     */
    public function testWebmasterReadsTheList(): void
    {
        $this->loginAsWebmaster();
        $response = $this->request('GET', self::LIST);

        self::assertSame(200, $response->getStatusCode(), self::serverLog());
        self::assertStringStartsWith('application/json', (string) $response->getHeader('Content-Type'));
        self::assertStringContainsString('no-store', (string) $response->getHeader('Cache-Control'));

        $list = self::decode($response->getContent());
        self::assertSame(['items', 'total', 'page', 'limit'], array_keys($list));
        self::assertSame(1, $list['page']);
        self::assertSame(15, $list['limit']);
        self::assertCount(15, $list['items']);
        self::assertSame(self::mimetypeCount(), $list['total']);
        self::assertSame(['id', 'title', 'url', 'fields'], array_keys($list['items'][0]));

        $ids = array_column($list['items'], 'id');
        $sorted = $ids;
        sort($sorted);
        self::assertSame($sorted, $ids, 'sorted by the identifier by default');

        $second = self::decode($this->request('GET', self::LIST . '?' . http_build_query(['page' => 2, 'limit' => 5]))->getContent());
        self::assertSame(2, $second['page']);
        self::assertSame(array_slice($ids, 5, 5), array_column($second['items'], 'id'));
    }

    /**
     * @scenario Anonymous visitor
     */
    public function testAnonymousVisitorIsNotAuthenticated(): void
    {
        foreach ([self::LIST, self::LIST . '/' . self::alphaId()] as $path) {
            $response = $this->request('GET', $path);

            self::assertSame(401, $response->getStatusCode(), self::serverLog());
            self::assertSame(['error'], array_keys(self::decode($response->getContent())));
            self::assertStringNotContainsString('icmsa', $response->getContent());
        }
    }

    /**
     * @scenario Administrator without the right
     */
    public function testAdministratorWithoutTheRightIsForbidden(): void
    {
        $this->loginAs(TestSite::MEDIAADMIN_UNAME, TestSite::MEDIAADMIN_PASS);

        foreach ([self::LIST, self::LIST . '/' . self::alphaId()] as $path) {
            $response = $this->request('GET', $path);

            self::assertSame(403, $response->getStatusCode(), self::serverLog());
            self::assertSame(['error'], array_keys(self::decode($response->getContent())));
            self::assertStringNotContainsString('icmsa', $response->getContent());
        }
    }

    public function testCategoryAdministratorReadsTheList(): void
    {
        $this->loginAs(TestSite::MIMETYPEADMIN_UNAME, TestSite::MIMETYPEADMIN_PASS);

        self::assertSame(200, $this->request('GET', self::LIST)->getStatusCode(), self::serverLog());
    }

    /**
     * @scenario Object without a declaration
     * @scenario Unknown module
     */
    public function testUndeclaredObjectsAreNotFound(): void
    {
        $this->loginAsWebmaster();

        foreach ([
            '/api/ipf/system/adsense',
            '/api/ipf/system/adsense/1',
            '/api/ipf/system/nosuchitem',
            '/api/ipf/nosuchmodule/mimetype',
            '/api/ipf/nosuchmodule/mimetype/1',
            '/api/ipf',
            '/api/other',
        ] as $path) {
            $response = $this->request('GET', $path);

            self::assertSame(404, $response->getStatusCode(), $path . "\n" . self::serverLog());
        }
    }

    /**
     * @scenario Search by name
     */
    public function testSearchLooksInTheDeclaredFields(): void
    {
        $this->loginAsWebmaster();

        $byName = self::decode($this->request('GET', self::LIST . '?' . http_build_query(['q' => 'Fixture Alpha']))->getContent());
        self::assertSame(1, $byName['total']);
        self::assertSame([self::alphaId()], array_column($byName['items'], 'id'));

        $byType = self::decode($this->request('GET', self::LIST . '?' . http_build_query(['q' => 'x-icms-', 'sort' => 'extension', 'order' => 'desc']))->getContent());
        self::assertSame(3, $byType['total']);
        self::assertSame(['icmsc', 'icmsb', 'icmsa'], array_map(static fn (array $item): string => $item['fields']['extension'], $byType['items']));

        $filtered = self::decode($this->request('GET', self::LIST . '?' . http_build_query(['filter' => ['extension' => 'icmsb']]))->getContent());
        self::assertSame(['ICMS Fixture Beta'], array_map(static fn (array $item): string => $item['fields']['name'], $filtered['items']));

        $none = self::decode($this->request('GET', self::LIST . '?' . http_build_query(['q' => 'no such mimetype anywhere']))->getContent());
        self::assertSame(['items' => [], 'total' => 0, 'page' => 1, 'limit' => 15], $none);
    }

    /**
     * @scenario Unknown sort field
     */
    public function testInvalidOptionsAreBadRequests(): void
    {
        $this->loginAsWebmaster();

        foreach ([
            ['sort' => 'no_such_field'],
            ['sort' => 'name, (SELECT 1)'],
            ['order' => 'sideways'],
            ['filter' => ['no_such_field' => '1']],
            ['limit' => 0],
            ['page' => 0],
        ] as $parameters) {
            $response = $this->request('GET', self::LIST . '?' . http_build_query($parameters));

            self::assertSame(400, $response->getStatusCode(), json_encode($parameters) . "\n" . self::serverLog());
            self::assertSame(['error'], array_keys(self::decode($response->getContent())));
        }

        $capped = self::decode($this->request('GET', self::LIST . '?' . http_build_query(['limit' => 100000]))->getContent());
        self::assertSame(100, $capped['limit']);
        self::assertLessThanOrEqual(100, count($capped['items']));
    }

    /**
     * @scenario Existing mimetype
     * @scenario Typed values
     */
    public function testDetailReturnsTypedValues(): void
    {
        $this->loginAsWebmaster();
        $id = self::alphaId();
        $response = $this->request('GET', self::LIST . '/' . $id);

        self::assertSame(200, $response->getStatusCode(), self::serverLog());
        $item = self::decode($response->getContent());

        self::assertSame($id, $item['id']);
        self::assertSame((string) $id, $item['title']);
        self::assertSame([
            'mimetypeid' => $id,
            'extension' => 'icmsa',
            'types' => 'application/x-icms-alpha',
            'name' => 'ICMS Fixture Alpha',
            'dirname' => ['system'],
        ], $item['fields']);
    }

    /**
     * @scenario Unknown id
     */
    public function testUnknownIdIsNotFound(): void
    {
        $this->loginAsWebmaster();

        foreach (['999999', '0', self::alphaId() . 'abc', 'abc'] as $id) {
            self::assertSame(404, $this->request('GET', self::LIST . '/' . $id)->getStatusCode(), $id);
        }
    }

    /**
     * @scenario POST to the list
     */
    public function testOtherMethodsAreRefused(): void
    {
        $this->loginAsWebmaster();
        $before = self::mimetypeCount();

        foreach ([['POST', self::LIST], ['DELETE', self::LIST . '/' . self::alphaId()], ['PUT', self::LIST . '/' . self::alphaId()]] as [$method, $path]) {
            $response = $this->request($method, $path, ['extension' => 'evil', 'name' => 'Evil', 'types' => 'x/evil']);

            self::assertSame(405, $response->getStatusCode(), "{$method} {$path}\n" . self::serverLog());
            self::assertSame('GET', $response->getHeader('Allow'));
        }

        self::assertSame($before, self::mimetypeCount());
        self::assertSame(200, $this->request('GET', self::LIST . '/' . self::alphaId())->getStatusCode());
    }

    /**
     * @return array<string, mixed>
     */
    private static function decode(string $json): array
    {
        return json_decode($json, true, 16, JSON_THROW_ON_ERROR);
    }

    private static function alphaId(): int
    {
        $stmt = TestSite::connection()->prepare('SELECT mimetypeid FROM ' . TestSite::table('system_mimetype') . ' WHERE extension = ?');
        $stmt->execute(['icmsa']);

        return (int) $stmt->fetchColumn();
    }

    private static function mimetypeCount(): int
    {
        return (int) TestSite::connection()->query('SELECT COUNT(*) FROM ' . TestSite::table('system_mimetype'))->fetchColumn();
    }
}
