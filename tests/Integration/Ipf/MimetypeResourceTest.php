<?php

declare(strict_types=1);

namespace App\Tests\Integration\Ipf;

use App\Ipf\Http\LegacyFormRejected;
use App\Ipf\Repository\LegacyHandlerRepository;
use App\Ipf\Resource\IpfResourceFactory;
use App\Ipf\Resource\LegacyResource;
use App\Ipf\Resource\ResourceRegistry;
use App\Legacy\HandlerLocator;
use App\Legacy\LegacyRuntime;
use App\Tests\Support\LegacyAppTestCase;
use App\Tests\Support\TestSite;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Modules\System\Generated\Mimetype\MimetypeDescriptor;
use Modules\System\Generated\Mimetype\MimetypeRequest;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\DomCrawler\Crawler;

/**
 * The mimetype resource against the real entity, handler and legacy IPF form:
 * the generated descriptor and FormRequest, and the legacy repository.
 *
 * Ported from the IpfFormsTest of the IpfForm stack, keeping its behavioural
 * assertions: the fields and order of the legacy form, captions and
 * descriptions, choices in order, the legacy form HTML validated as posted,
 * caption-named violations, nothing stored on failure, handler events in
 * legacy order, permissions replaced (none when not sent).
 */
#[RunTestsInSeparateProcesses]
#[Group('spec:ipf-forms')]
final class MimetypeResourceTest extends LegacyAppTestCase
{
    private const ALPHA = 124;

    protected function setUp(): void
    {
        parent::setUp();
        // the legacy database layer only writes during a same-site POST
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_REFERER'] = 'http://127.0.0.1/';
        $this->app->make(LegacyRuntime::class)->bootTo('online');
    }

    /**
     * @scenario Mimetype entity
     */
    public function testFormFieldsAreThoseOfTheLegacyForm(): void
    {
        $legacy = $this->legacyElements($this->handler()->get(self::ALPHA));
        // the page before the form is navigation, not entity data
        unset($legacy['icms_page_before_form']);

        self::assertSame(array_keys($legacy), MimetypeDescriptor::form());
        self::assertTrue(MimetypeDescriptor::field('mimetypeid')['hidden']);

        $resource = $this->resource()->legacy;
        foreach (['extension', 'types', 'name', 'dirname', 'use_extension'] as $name) {
            self::assertSame(strip_tags($legacy[$name]->getCaption()), $resource->caption($name), $name);
            self::assertSame($legacy[$name]->getDescription() ?: null, $resource->description($name), $name);
        }

        $record = $this->resource()->repository->find(self::ALPHA);
        self::assertSame([1, 2], $record?->groups('use_extension'));
        self::assertSame(['system'], $record?->value('dirname'));
        self::assertSame('icmsa', $record?->value('extension'));
    }

    /**
     * @scenario Select-multi control with an item handler
     */
    public function testChoicesAreTheLegacyOptionsInOrder(): void
    {
        $options = $this->resource()->legacy->options('dirname');
        $active = \icms::handler('icms_module')->getActive();

        self::assertNotEmpty($active);
        self::assertSame(array_map('strval', array_keys($active)), array_map('strval', array_keys($options)));
        self::assertSame($this->legacyElements($this->handler()->get(self::ALPHA))['dirname']->getOptions(), $options);
        self::assertTrue(MimetypeDescriptor::field('dirname')['multiple']);
    }

    /**
     * @scenario Legacy form posted to the framework form
     */
    public function testLegacyFormHtmlIsValidatedAsPosted(): void
    {
        $mimetype = $this->handler()->get(self::ALPHA);
        $html = '<html><body>' . $mimetype->getForm('caption', 'addmimetype')->render() . '</body></html>';
        $form = (new Crawler($html, 'http://127.0.0.1/modules/system/admin.php'))->filterXPath('//form[@id="addmimetype"]')->form();
        $form['extension'] = 'icmsz';
        $form['name'] = 'Renamed';
        $form['types'] = 'application/x-icms-zeta';
        $form['use_extension']->select(['1', '3']);

        $request = $this->validate($form->getPhpValues());

        self::assertSame(['extension' => 'icmsz', 'types' => 'application/x-icms-zeta', 'name' => 'Renamed', 'dirname' => ['system']], $request->values());
        self::assertSame(['use_extension' => [1, 3]], $request->permissionGroups());
        self::assertArrayNotHasKey('mimetypeid', $request->values(), 'the key is not written from the form');
        self::assertArrayNotHasKey('op', $request->validated(), 'only the form fields');
    }

    /**
     * @scenario Required field left empty
     */
    public function testViolationsNameTheFieldCaption(): void
    {
        $violations = $this->violations(['extension' => '']);
        // the mimetype language constants are defined once the handler is loaded
        self::assertSame([sprintf(_XOBJ_ERR_REQUIRED, _CO_ICMS_MIMETYPE_EXTENSION)], $violations);
        self::assertStringContainsString(_CO_ICMS_MIMETYPE_DIRNAME, implode("\n", $this->violations(['dirname' => ['no-such-module']])));
        self::assertStringContainsString(_CO_ICMS_MIMETYPE_PERMISSION_VIEW, implode("\n", $this->violations(['use_extension' => ['999']])));
        self::assertSame([], $this->violations(['name' => 'Only the name']), 'fields that are not sent are not validated');
        self::assertSame(['extension' => 'icmsa'], self::row(self::ALPHA, ['extension']), 'a rejected submission stores nothing');
    }

    /**
     * The legacy query string is ignored: only the POST body is the form.
     */
    public function testOnlyThePostBodyIsValidated(): void
    {
        $request = $this->validate(['name' => 'From the body'], ['extension' => '']);

        self::assertSame(['name' => 'From the body'], $request->values());
    }

    /**
     * @scenario Storing a new mimetype
     * @scenario Mimetype upload permission
     */
    public function testSaveRunsTheHandlerEventsInLegacyOrderAndReplacesThePermissions(): void
    {
        $handler = $this->recordingHandler();
        $repository = new LegacyHandlerRepository($this->legacyResource($handler));

        $result = $repository->save(null, ['extension' => 'icmsd', 'types' => 'application/x-icms-delta', 'name' => 'ICMS Fixture Delta', 'dirname' => ['system']], ['use_extension' => [1, 2]]);
        self::assertTrue($result->succeeded(), implode("\n", $result->errors));
        self::assertTrue($result->created);
        self::assertSame(['beforeSave', 'beforeInsert', 'afterSave', 'afterInsert'], $handler->events);
        $id = (int) $result->id;
        self::assertSame(['extension' => 'icmsd', 'name' => 'ICMS Fixture Delta', 'dirname' => 'system'], self::row($id, ['extension', 'name', 'dirname']));
        self::assertSame([1, 2], self::useExtensionGroups($id));

        $handler->events = [];
        $result = $repository->save($id, ['name' => 'Renamed'], ['use_extension' => [2, 3]]);
        self::assertFalse($result->created);
        self::assertSame(['beforeSave', 'beforeUpdate', 'afterSave', 'afterUpdate'], $handler->events);
        self::assertSame(['extension' => 'icmsd', 'name' => 'Renamed'], self::row($id, ['extension', 'name']), 'values not given are kept');
        self::assertSame([2, 3], self::useExtensionGroups($id), 'the groups replace the previous ones');

        // a selection the browser did not send means no groups, as in legacy
        $repository->save($id, ['name' => 'Renamed again'], []);
        self::assertSame([], self::useExtensionGroups($id));

        $handler->events = [];
        self::assertTrue($repository->delete($id)->succeeded());
        self::assertSame(['beforeDelete', 'afterDelete'], $handler->events);
    }

    public function testTheHandlersErrorsAreReturned(): void
    {
        $handler = $this->recordingHandler(refuse: true);
        $result = (new LegacyHandlerRepository($this->legacyResource($handler)))->save(null, ['extension' => 'icmse', 'types' => 'x', 'name' => 'E', 'dirname' => ['system']], []);

        self::assertFalse($result->succeeded());
        self::assertNotSame([], $result->errors);
        self::assertNull(self::row(0, ['extension']) ?: null);
    }

    public function testDeleteRemovesTheMimetypeAndItsPermissions(): void
    {
        self::assertSame([1, 2], self::useExtensionGroups(self::ALPHA));

        self::assertTrue($this->resource()->repository->delete(self::ALPHA)->succeeded());

        self::assertNull($this->resource()->repository->find(self::ALPHA));
        self::assertSame([], self::useExtensionGroups(self::ALPHA));
        self::assertFalse($this->resource()->repository->delete(self::ALPHA)->found, 'an unknown item is reported as not found');
    }

    /**
     * @param array<string, mixed> $body
     * @param array<string, mixed> $query
     */
    private function validate(array $body, array $query = []): MimetypeRequest
    {
        $request = MimetypeRequest::createFrom(Request::create('/modules/system/admin.php?' . http_build_query($query), 'POST', $body));
        $request->setContainer($this->app)->setRedirector($this->app->make(Redirector::class));
        $request->validateResolved();

        return $request;
    }

    /**
     * @param array<string, mixed> $body
     * @return list<string>
     */
    private function violations(array $body): array
    {
        try {
            $this->validate($body);
        } catch (LegacyFormRejected $e) {
            return $e->errors;
        }

        return [];
    }

    private function resource(): \App\Ipf\Resource\IpfResource
    {
        return $this->app->make(IpfResourceFactory::class)->get('system.mimetype');
    }

    private function handler(): object
    {
        return $this->app->make('icms.handlers')->get('module:system/mimetype');
    }

    private function legacyResource(object $handler): LegacyResource
    {
        return new LegacyResource(
            $this->app->make(ResourceRegistry::class)->get('system.mimetype'),
            MimetypeDescriptor::class,
            $this->app->make(HandlerLocator::class),
            $this->app->make(LegacyRuntime::class),
            $handler,
        );
    }

    /**
     * @return array<string, object> element name => element of the legacy form
     */
    private function legacyElements(object $mimetype): array
    {
        $elements = [];
        foreach ($mimetype->getForm('caption', 'addmimetype')->getElements(true) as $element) {
            $elements[$element->getName(false)] = $element;
        }

        return $elements;
    }

    /**
     * A mimetype handler that records the events the legacy handler runs.
     */
    private function recordingHandler(bool $refuse = false): object
    {
        $this->handler();

        $handler = new class (\icms::$xoopsDB) extends \SystemMimetypeHandler {
            /** @var list<string> */
            public array $events = [];

            public bool $refuse = false;

            public function beforeSave(object $obj): bool
            {
                return $this->record(__FUNCTION__) && ! $this->refuse;
            }

            public function beforeInsert(object $obj): bool
            {
                return $this->record(__FUNCTION__);
            }

            public function beforeUpdate(object $obj): bool
            {
                return $this->record(__FUNCTION__);
            }

            public function afterSave(object $obj): bool
            {
                return $this->record(__FUNCTION__);
            }

            public function afterInsert(object $obj): bool
            {
                return $this->record(__FUNCTION__);
            }

            public function afterUpdate(object $obj): bool
            {
                return $this->record(__FUNCTION__);
            }

            public function beforeDelete(object $obj): bool
            {
                return $this->record(__FUNCTION__);
            }

            public function afterDelete(object $obj): bool
            {
                return $this->record(__FUNCTION__);
            }

            private function record(string $event): bool
            {
                $this->events[] = $event;

                return true;
            }
        };
        $handler->refuse = $refuse;

        return $handler;
    }

    /**
     * @param list<string> $columns
     * @return array<string, string>
     */
    private static function row(int $id, array $columns): array
    {
        $stmt = TestSite::connection()->prepare('SELECT ' . implode(', ', $columns) . ' FROM ' . TestSite::table('system_mimetype') . ' WHERE mimetypeid = ?');
        $stmt->execute([$id]);

        return array_map('strval', (array) ($stmt->fetch(\PDO::FETCH_ASSOC) ?: []));
    }

    /**
     * @return list<int>
     */
    private static function useExtensionGroups(int $id): array
    {
        $stmt = TestSite::connection()->prepare(
            'SELECT gperm_groupid FROM ' . TestSite::table('group_permission')
            . " WHERE gperm_name = 'use_extension' AND gperm_modid = 1 AND gperm_itemid = ? ORDER BY gperm_groupid",
        );
        $stmt->execute([$id]);

        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }
}
