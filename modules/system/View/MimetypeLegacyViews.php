<?php

declare(strict_types=1);

namespace Modules\System\View;

use App\Admin\AdminPageRenderer;
use App\Ipf\Resource\IpfResourceFactory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The mimetype pages as legacy renders them (admin/mimetype/main.php): the
 * IPF table and form in db:system_adm_mimetype.html, and the IPF deletion
 * confirmation. Used when the resource's renderer is `legacy`, the rollback
 * of the Blade rendering; the output matches the legacy page, plus the
 * security token of the form.
 */
final class MimetypeLegacyViews
{
    private const TEMPLATE = 'db:system_adm_mimetype.html';

    public function __construct(
        private readonly AdminPageRenderer $renderer,
        private readonly IpfResourceFactory $resources,
    ) {
    }

    public function index(): Response
    {
        $handler = $this->handler();

        return $this->renderer->render(self::TEMPLATE, static function (object $template) use ($handler): void {
            $table = new \Icms\Ipf\View\Table($handler);
            $table->addColumn(new \Icms\Ipf\View\Column('name', _GLOBAL_LEFT, 150));
            $table->addColumn(new \Icms\Ipf\View\Column('extension', _GLOBAL_LEFT, 150));
            $table->addColumn(new \Icms\Ipf\View\Column('types', _GLOBAL_LEFT));
            $table->addIntroButton('addmimetype', 'admin.php?fct=mimetype&amp;op=mod', _CO_ICMS_MIMETYPE_CREATE);
            $table->addQuickSearch(['name', 'extension', 'types']);

            $template->assign('icms_mimetype_table', $table->fetch());
            $template->assign('icms_mimetype_explain', true);
            $template->assign('icms_mimetype_title', _CO_ICMS_MIMETYPES_DSC);
        });
    }

    public function form(?int $id, string $caption, string $title): Response
    {
        $handler = $this->handler();

        return $this->renderer->render(self::TEMPLATE, static function (object $template) use ($handler, $id, $caption, $title): void {
            $mimetype = $handler->get((int) $id);
            if ($mimetype->isNew()) {
                $mimetype->setVar('mimetypeid', 0);
                $mimetype->setVar('extension', '');
            }

            $mimetype->getSecureForm($caption, 'addmimetype')->assign($template);
            $template->assign('icms_mimetype_title', $title);
        });
    }

    public function confirmDeletion(int $id, Request $request): Response
    {
        $handler = $this->handler();
        $mimetype = $handler->get($id);
        $key = $handler->keyName;
        $hiddens = [
            'op' => 'del',
            $key => $mimetype->getVar($key),
            'confirm' => 1,
            'redirect_page' => \icms::$urls['previouspage'],
            'fct' => $request->query->get('fct', false),
        ];

        return $this->renderer->renderFragment(static function () use ($hiddens, $mimetype, $handler): void {
            \Icms\Core\Message::confirm(
                $hiddens,
                (string) xoops_getenv('SCRIPT_NAME'),
                sprintf(_CO_ICMS_DELETE_CONFIRM, $mimetype->getVar($handler->identifierName)),
                _CO_ICMS_DELETE,
            );
        });
    }

    private function handler(): object
    {
        return $this->resources->get('system.mimetype')->legacy->handler();
    }
}
