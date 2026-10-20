<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Front\Page;
use App\Legacy\Dbal\Config;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

/**
 * Opening a login link (OpenSpec change remember-me-and-email-links, design
 * D2): shows a button that posts to the same URL, where Symfony's login link
 * authenticator logs in (check_post_only). Opening alone, as mail scanners
 * do, logs nobody in.
 */
final class Link
{
    public function __construct(
        private readonly Page $page,
        private readonly Environment $twig,
        private readonly Config $config,
    ) {
    }

    /** POST is the login itself: the login link authenticator answers it before this controller. */
    #[Route('/auth/link/check', name: 'icms_auth_link_check', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $reset = $request->query->get('reset') === '1';
        $siteName = (string) $this->config->get('sitename', '');
        $action = $request->getRequestUri();

        // the language constants exist once host_page.php booted the page
        return $this->page->render(function () use ($reset, $siteName, $action): array {
            $title = (string) constant('_US_LINKCONFIRM_TITLE');
            $text = $reset
                ? (string) constant('_US_RESETCONFIRM_TEXT')
                : sprintf((string) constant('_US_LINKCONFIRM_TEXT'), $siteName);

            return ['title' => $title, 'html' => $this->twig->render('auth/confirm.html.twig', [
                'title' => $title,
                'text' => $text,
                'action' => $action,
                'button' => $title,
            ])];
        });
    }
}
