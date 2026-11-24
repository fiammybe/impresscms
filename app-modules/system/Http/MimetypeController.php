<?php

declare(strict_types=1);

namespace Modules\System\Http;

use App\Admin\LegacyNavigation;
use App\Ipf\Http\LegacyFormRejected;
use App\Ipf\Repository\IpfRecord;
use App\Ipf\Repository\ListQuery;
use App\Ipf\Resource\ForResource;
use App\Ipf\Resource\IpfResource;
use App\Ipf\View\AdminViews;
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;
use Modules\System\Generated\Mimetype\MimetypeRequest;
use Modules\System\View\MimetypeLegacyViews;
use Symfony\Component\HttpFoundation\Response;

/**
 * The mimetype administration of the system module, a resource controller.
 *
 * Its legacy URL, `modules/system/admin.php?fct=mimetype&op=…`, reaches these
 * actions through App\Ipf\Http\LegacyOperationsController; validation is the
 * generated MimetypeRequest, storage the resource's repository, access the
 * generated MimetypePolicy. Pages render with the IPF Blade components, or
 * with the legacy IPF form and table when the resource's renderer is legacy.
 *
 * Rollback: ICMS_TAKEOVERS_DISABLED=system.mimetype serves the legacy page;
 * the switches in modules/system/config.php roll back single concerns.
 */
final class MimetypeController
{
    public function __construct(
        #[ForResource('system.mimetype')]
        private readonly IpfResource $mimetypes,
        private readonly LegacyNavigation $navigation,
        private readonly AdminViews $views,
        private readonly MimetypeLegacyViews $legacyViews,
    ) {
    }

    public function index(Request $request): Response
    {
        $this->mimetypes->authorize('viewAny');
        if (! $this->mimetypes->rendersWithBlade()) {
            return $this->legacyViews->index();
        }

        $page = $this->mimetypes->repository->paginate(ListQuery::fromRequest($this->mimetypes->descriptor, $request));
        $list = $this->views->list($this->mimetypes, $page, $request, $this->mimetypes->text('_CO_ICMS_MIMETYPE_CREATE'));

        return $this->page('_CO_ICMS_MIMETYPES_DSC', true, $list);
    }

    public function create(): Response
    {
        $this->mimetypes->authorize('create');

        return $this->form($this->mimetypes->repository->make(), '_CO_ICMS_MIMETYPE_CREATE', '_CO_ICMS_MIMETYPE_CREATE_INFO');
    }

    public function edit(int $id): Response
    {
        $record = $this->mimetypes->repository->find($id);
        if ($record === null) {
            // as legacy: an unknown id shows the create form
            return $this->create();
        }

        $this->mimetypes->authorize('update', $id);

        return $this->form($record, '_CO_ICMS_MIMETYPE_EDIT', '_CO_ICMS_MIMETYPE_EDIT_INFO');
    }

    public function store(MimetypeRequest $request): Response
    {
        $this->mimetypes->authorize('create');

        return $this->save(null, $request);
    }

    public function update(MimetypeRequest $request, int $id): Response
    {
        $this->mimetypes->authorize('update', $id);

        return $this->save($id, $request);
    }

    public function confirmDestroy(Request $request, int $id): Response
    {
        $record = $this->mimetypes->repository->find($id);
        if ($record === null) {
            return $this->notSelected();
        }

        $this->mimetypes->authorize('delete', $id);
        if (! $this->mimetypes->rendersWithBlade()) {
            return $this->legacyViews->confirmDeletion($id, $request);
        }

        return $this->views->confirmDeletion($this->mimetypes, $record, $this->navigation->previousPage(), $request);
    }

    public function destroy(Request $request, int $id): Response
    {
        $this->mimetypes->authorize('delete', $id);

        $result = $this->mimetypes->repository->delete($id);
        if (! $result->found) {
            return $this->notSelected();
        }

        $target = (string) $request->request->get('redirect_page', '');
        if (! $result->succeeded()) {
            return $this->navigation->redirect($target, 3, LegacyNavigation::deleteErrorMessage($result->errors));
        }

        return $this->navigation->redirect($target, 3, $this->mimetypes->text('_CO_ICMS_DELETE_SUCCESS'));
    }

    private function form(IpfRecord $record, string $caption, string $title): Response
    {
        if (! $this->mimetypes->rendersWithBlade()) {
            return $this->legacyViews->form($record->id, $this->mimetypes->text($caption), $this->mimetypes->text($title));
        }

        return $this->page($title, false, $this->views->form($this->mimetypes, $record, $this->mimetypes->text($caption)));
    }

    /**
     * db:system_adm_mimetype.html around a list or a form.
     */
    private function page(string $title, bool $explain, HtmlString $content): Response
    {
        return $this->views->render('system::mimetype.page', [
            'siteUrl' => $this->navigation->siteUrl(),
            'heading' => $this->mimetypes->text('_CO_ICMS_MIMETYPES'),
            'explain' => $explain,
            'explainTitle' => $this->mimetypes->text('_CO_ICMS_MIMETYPES_EXPLAIN_TITLE'),
            'explainText' => $this->mimetypes->text('_CO_ICMS_MIMETYPES_EXPLAIN'),
            'title' => $this->mimetypes->text($title),
            'content' => $content,
        ]);
    }

    private function save(?int $id, MimetypeRequest $request): Response
    {
        $result = $this->mimetypes->repository->save($id, $request->values(), $request->permissionGroups());
        if (! $result->succeeded()) {
            throw new LegacyFormRejected($result->errors);
        }

        $message = $this->mimetypes->text($result->created ? '_CO_ICMS_MIMETYPE_CREATED' : '_CO_ICMS_MIMETYPE_MODIFIED');

        return $this->navigation->redirect($this->navigation->pageBeforeForm(), 2, $message);
    }

    private function notSelected(): Response
    {
        return $this->navigation->redirect('javascript:history.go(-1)', 3, $this->mimetypes->text('_CO_ICMS_NOT_SELECTED'));
    }
}
