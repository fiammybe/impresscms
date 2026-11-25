<?php

declare(strict_types=1);

namespace App\Tests\Integration\Ipf;

use App\Ipf\Repository\EloquentReadRepository;
use App\Ipf\Repository\LegacyHandlerRepository;
use App\Ipf\Repository\ListQuery;
use App\Ipf\Resource\LegacyResource;
use App\Ipf\Resource\ResourceRegistry;
use App\Legacy\HandlerLocator;
use App\Legacy\LegacyRuntime;
use App\Tests\Support\LegacyAppTestCase;
use Illuminate\Http\Request;
use LogicException;
use Modules\System\Generated\Mimetype\Mimetype;
use Modules\System\Generated\Mimetype\MimetypeDescriptor;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * The generated Eloquent model and the IPF handler on the same rows: what the
 * handler writes, the model reads identically (table prefix, legacy column
 * names, `|`-joined lists, permissions); the model refuses to write.
 */
#[RunTestsInSeparateProcesses]
#[Group('spec:ipf-persistence')]
final class EloquentParityTest extends LegacyAppTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_REFERER'] = 'http://127.0.0.1/';
        $this->app->make(LegacyRuntime::class)->bootTo('online');
    }

    public function testBothRepositoriesReadWhatTheHandlerWrote(): void
    {
        [$legacy, $eloquent] = $this->repositories();
        $saved = $legacy->save(null, ['extension' => 'icmsp', 'types' => 'application/x-icms-parity', 'name' => 'Parity', 'dirname' => ['system', 'other']], ['use_extension' => [2, 3]]);
        self::assertTrue($saved->succeeded(), implode("\n", $saved->errors));

        $fromHandler = $legacy->find((int) $saved->id);
        $fromModel = $eloquent->find((int) $saved->id);

        self::assertNotNull($fromModel);
        self::assertEquals($fromHandler, $fromModel);
        self::assertSame(['system', 'other'], $fromModel->value('dirname'));
        self::assertSame([2, 3], $fromModel->groups('use_extension'));
    }

    public function testListsAgree(): void
    {
        [$legacy, $eloquent] = $this->repositories();
        foreach (['', 'quicksearch_SystemMimetype=icms&mimetype_sortsel=extension&mimetype_ordersel=DESC', 'limitsel=2&startmimetypeid=2'] as $query) {
            $listQuery = ListQuery::fromRequest(MimetypeDescriptor::class, Request::create('/?' . $query . '&fct=mimetype'));
            $fromHandler = $legacy->paginate($listQuery);
            $fromModel = $eloquent->paginate($listQuery);

            self::assertSame($fromHandler->total, $fromModel->total, $query);
            self::assertEquals($fromHandler->records, $fromModel->records, $query);
        }
    }

    public function testTheModelIsReadOnly(): void
    {
        $model = Mimetype::query()->firstOrFail();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('read-only');
        $model->forceFill(['name' => 'changed'])->save();
    }

    /**
     * @return array{LegacyHandlerRepository, EloquentReadRepository}
     */
    private function repositories(): array
    {
        $resource = new LegacyResource(
            $this->app->make(ResourceRegistry::class)->get('system.mimetype'),
            MimetypeDescriptor::class,
            $this->app->make(HandlerLocator::class),
            $this->app->make(LegacyRuntime::class),
        );
        $legacy = new LegacyHandlerRepository($resource);

        return [$legacy, new EloquentReadRepository($legacy, Mimetype::class, MimetypeDescriptor::class, $this->app->make('db')->connection('icms'))];
    }
}
