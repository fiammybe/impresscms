<?php

declare(strict_types=1);

namespace App\Controller\System;

use App\Admin\AdminPageRenderer;
use App\Admin\Attribute\SystemAdminFunction;
use App\Admin\LegacyRedirect;
use App\Admin\UploadReferences;
use Icms\Ipf\DiskFile\Entity;
use Icms\Ipf\DiskFile\Exception as DiskFileException;
use Icms\Ipf\DiskFile\FolderTree;
use Icms\Ipf\DiskFile\Handler;
use Icms\Ipf\DiskFile\TreeNode;
use Icms\Ipf\DiskFile\TreeView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The asset manager of the system module, `modules/system/admin.php?fct=assets`
 * (design D1 to D6): the folders and files of the upload folder as a tree, and
 * the deletion of single files.
 *
 * The page is new, so there is no legacy version to fall back to. Output is
 * legacy: the content template, the table template of the IPF tables and the
 * admin theme. Deleting needs a confirmation and an ImpressCMS security token.
 */
#[SystemAdminFunction('assets')]
final class AssetsController
{
    public const ROUTE = 'system_assets';

    private const TEMPLATE = 'db:system_adm_assets.html';
    private const TABLE_TEMPLATE = 'db:system_persistabletable_display.html';
    private const PROTECTED_NAMES = ['index.html', 'blank.gif'];

    public function __construct(
        private readonly AdminPageRenderer $renderer,
        private readonly LegacyRedirect $redirect,
        private readonly UploadReferences $references,
    ) {
    }

    /**
     * `fct` from the query string, overridden by the POST body: the delete
     * confirmation posts it without a query string.
     */
    #[Route(
        '/modules/system/admin.php',
        name: self::ROUTE,
        methods: ['GET', 'POST'],
        condition: "(request.request.has('fct') ? request.request.get('fct') : request.query.get('fct')) == 'assets'",
    )]
    public function __invoke(Request $request): Response
    {
        $op = (string) $request->request->get('op', (string) $request->query->get('op', ''));

        return match ($op) {
            'del' => $this->delete($request),
            default => $this->list(),
        };
    }

    public static function isProtected(string $baseName): bool
    {
        return str_starts_with($baseName, '.') || in_array(strtolower($baseName), self::PROTECTED_NAMES, true);
    }

    private function list(): Response
    {
        $handler = new Handler(ICMS_UPLOAD_PATH);

        return $this->renderer->render(self::TEMPLATE, function (object $template) use ($handler): void {
            $view = new TreeView(
                new FolderTree($handler),
                $handler->root(),
                [
                    'basename' => _CO_ICMS_ASSETS_NAME,
                    'size' => _CO_ICMS_ASSETS_SIZE,
                    'mtime' => _CO_ICMS_ASSETS_MODIFIED,
                ],
            );
            $view->addAction(fn (TreeNode $node): string => $this->deleteLink($handler, $node));

            $table = new \Icms\View\Tpl();
            $table->assign($view->templateVariables());

            $template->assign('icms_assets_table', $table->fetch(self::TABLE_TEMPLATE));
            $template->assign('icms_assets_title', _CO_ICMS_ASSETS_DSC);
        });
    }

    private function deleteLink(Handler $handler, TreeNode $node): string
    {
        if ($node->isFolder() || self::isProtected($node->name)) {
            return '';
        }

        $url = sprintf('%s&amp;op=del&amp;path=%s', $this->pageUrl(), rawurlencode($this->relativePath($handler, $node->path)));

        return sprintf(
            "<a href='%s'><img src='%s/actions/editdelete.png' style='vertical-align: middle;' alt='%s' title='%s'/></a>",
            $url,
            ICMS_IMAGES_SET_URL,
            _CO_ICMS_ASSETS_DELETE,
            _CO_ICMS_ASSETS_DELETE,
        );
    }

    /**
     * The confirmation, then the deletion of a confirmed POST with a valid token.
     * Only the POST body can confirm, so a link cannot delete.
     */
    private function delete(Request $request): Response
    {
        $relative = (string) $request->request->get('path', (string) $request->query->get('path', ''));
        $handler = new Handler(ICMS_UPLOAD_PATH);

        $file = $this->find($handler, $relative);
        if ($file === null) {
            return $this->back(_CO_ICMS_ASSETS_NOT_SELECTED);
        }

        if (self::isProtected((string) $file->getVar('basename', 'n'))) {
            return $this->back(_CO_ICMS_ASSETS_PROTECTED);
        }

        if (!$request->request->get('confirm')) {
            return $this->confirmation($relative);
        }

        if (!\icms::$security->check()) {
            return $this->back(_CORE_TOKENINVALID . '<br />' . implode('<br />', (array) \icms::$security->getErrors()));
        }

        try {
            $file->delete();
        } catch (DiskFileException) {
            return $this->back(_CO_ICMS_ASSETS_DELETE_FAILED);
        }

        return $this->back(_CO_ICMS_ASSETS_DELETED);
    }

    private function confirmation(string $relative): Response
    {
        $message = sprintf(_CO_ICMS_ASSETS_DELETE_CONFIRM, htmlspecialchars($relative, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));

        $kinds = $this->references->find($relative);
        if ($kinds !== []) {
            $message .= '<br />' . sprintf(_CO_ICMS_ASSETS_IN_USE, implode(', ', $kinds));
        }

        $hiddens = ['op' => 'del', 'path' => $relative, 'confirm' => 1, 'fct' => 'assets'];

        return $this->renderer->renderFragment(static function () use ($hiddens, $message): void {
            \Icms\Core\Message::confirm($hiddens, ICMS_MODULES_URL . '/system/admin.php', $message, _CO_ICMS_DELETE);
        });
    }

    /**
     * The file at a path below the upload folder, or null for anything the
     * handler does not accept: a folder, a missing file, a path that leaves the
     * upload folder.
     */
    private function find(Handler $handler, string $relative): ?Entity
    {
        if ($relative === '' || str_contains($relative, "\0")) {
            return null;
        }

        try {
            return $handler->get($handler->root() . DIRECTORY_SEPARATOR . $relative);
        } catch (DiskFileException) {
            return null;
        }
    }

    private function relativePath(Handler $handler, string $path): string
    {
        return str_replace('\\', '/', substr($path, strlen($handler->root()) + 1));
    }

    private function pageUrl(): string
    {
        return ICMS_MODULES_URL . '/system/admin.php?fct=assets';
    }

    private function back(string $message): Response
    {
        return $this->redirect->to($this->pageUrl(), 3, $message);
    }
}
