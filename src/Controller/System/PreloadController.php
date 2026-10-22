<?php

declare(strict_types=1);

namespace App\Controller\System;

use App\Admin\AdminPageRenderer;
use App\Admin\Attribute\SystemAdminFunction;
use App\Admin\LegacyRedirect;
use App\Preload\DisabledPreloads;
use App\Preload\PreloadInfo;
use App\Preload\Preloads;
use App\Preload\PreloadStoreException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * The preloads administration of the system module (design D1, D6, D7): the
 * installed preloads with their events, and switching them off and on. It has
 * no legacy page; admin.php?fct=preloads redirects here.
 *
 * At routing level 1 only index.php/<route> reaches the host, so links are
 * generated for the URL the request came in on.
 */
#[SystemAdminFunction('preloads')]
final class PreloadController
{
    public const ROUTE = 'system_preloads';
    public const TOGGLE_ROUTE = 'system_preloads_toggle';

    /** Preloads whose name gets a warning: disabling them weakens the site's security. */
    private const SECURITY_PRELOADS = ['protector'];

    public function __construct(
        private readonly AdminPageRenderer $renderer,
        private readonly LegacyRedirect $redirect,
        private readonly Preloads $preloads,
        private readonly UrlGeneratorInterface $urls,
    ) {
    }

    #[Route('/admin/preloads', name: self::ROUTE, methods: ['GET'])]
    public function list(): Response
    {
        $preloads = array_map(self::row(...), $this->preloads->inventory()->all());
        $toggleUrl = $this->urls->generate(self::TOGGLE_ROUTE);

        return $this->renderer->render(self::template(), static function (object $template) use ($preloads, $toggleUrl): void {
            $template->assign('icms_preloads', $preloads);
            $template->assign('icms_preloads_toggle_url', $toggleUrl);
            $template->assign('icms_preloads_token', \icms::$security->getTokenHTML());
        });
    }

    #[Route('/admin/preloads/toggle', name: self::TOGGLE_ROUTE, methods: ['POST'])]
    public function toggle(Request $request): Response
    {
        $listUrl = $this->urls->generate(self::ROUTE);

        if (!\icms::$security->check()) {
            return $this->redirect->to(
                $listUrl,
                3,
                _CORE_TOKENINVALID . '<br />' . implode('<br />', (array) \icms::$security->getErrors()),
            );
        }

        $name = (string) $request->request->get('preload', '');
        $state = (string) $request->request->get('state', '');
        $preload = $this->preloads->inventory()->find($name);

        if ($preload === null || !DisabledPreloads::isValidName($name)) {
            return $this->redirect->to($listUrl, 3, sprintf(_MD_AM_PRELOADS_UNKNOWN, htmlspecialchars($name, ENT_QUOTES)));
        }

        if (!in_array($state, ['enable', 'disable'], true)) {
            return $this->redirect->to($listUrl, 3, sprintf(_MD_AM_PRELOADS_UNKNOWN, htmlspecialchars($name, ENT_QUOTES)));
        }

        $store = $this->preloads->disabled();

        try {
            $state === 'disable'
                ? $store->disable($name)
                : $store->enable($name);
        } catch (PreloadStoreException $e) {
            return $this->redirect->to($listUrl, 5, sprintf(_MD_AM_PRELOADS_NOTWRITABLE, htmlspecialchars($e->directory, ENT_QUOTES)));
        }

        $message = $state === 'disable' ? _MD_AM_PRELOADS_DISABLEDOK : _MD_AM_PRELOADS_ENABLEDOK;

        return $this->redirect->to($listUrl, 2, sprintf($message, $preload->name));
    }

    /**
     * The content template, read from the system module's templates: a `db:`
     * template only exists after the system module has been updated.
     */
    private static function template(): string
    {
        return 'file:' . ICMS_ROOT_PATH . '/modules/system/templates/system_adm_preloads.html';
    }

    /**
     * @return array{
     *     name: string,
     *     events: list<string>,
     *     enabled: bool,
     *     security: bool,
     *     toggleable: bool
     * }
     */
    private static function row(PreloadInfo $preload): array
    {
        return [
            'name' => $preload->name,
            'events' => $preload->events,
            'enabled' => $preload->enabled,
            'security' => in_array(strtolower($preload->name), self::SECURITY_PRELOADS, true),
            'toggleable' => DisabledPreloads::isValidName($preload->name),
        ];
    }
}
