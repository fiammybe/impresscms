<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Legacy\Dbal\Config;
use App\Legacy\LegacyRuntime;
use App\Mail\Sender;
use App\Security\Login\Responder;
use App\Security\User\Entity as User;
use App\Security\User\Provider as Users;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

/**
 * The lost-password box of the login page (OpenSpec change
 * remember-me-and-email-links, design D3): takes over /lostpass.php. With
 * op=loginlink it mails a login link, otherwise a reset link (a login link
 * that leads to the new-password form). Never a password; the answer is the
 * same for known and unknown addresses; 3 mails per 15 minutes per address
 * and IP.
 */
final class Mail
{
    /** The lifetime of the links (security.yaml, login_link) */
    private const MINUTES = 15;

    public function __construct(
        private readonly Users $users,
        private readonly LoginLinkHandlerInterface $links,
        private readonly Sender $sender,
        private readonly Config $config,
        private readonly LegacyRuntime $runtime,
        private readonly Responder $responder,
        #[Autowire(service: 'limiter.icms_mail_link')]
        private readonly RateLimiterFactory $limiter,
    ) {
    }

    #[Route('/lostpass.php', name: 'icms_auth_mail', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $loginPage = $request->getBasePath() . '/user.php';
        if (!$request->isMethod('POST')) {
            return new RedirectResponse($loginPage . '#lost');
        }

        $this->runtime->bootTo('language');
        \icms_loadLanguageFile('core', 'user');
        $email = strtolower(trim((string) $request->request->get('email', '')));
        if (Responder::sameSite($request) && filter_var($email, FILTER_VALIDATE_EMAIL) !== false
            && $this->limiter->create($email . '|' . $request->getClientIp())->consume()->isAccepted()) {
            $this->mail($request, $email, $request->request->get('op') === 'loginlink');
        }

        return $this->responder->redirect($request, $loginPage, (string) constant('_US_LINKSENT'));
    }

    private function mail(Request $request, string $email, bool $loginLink): void
    {
        $site = (string) $this->config->get('sitename', '');
        try {
            $user = $this->users->loadUserByIdentifier($email);
        } catch (UserNotFoundException) {
            $user = null;
        }

        if ($user instanceof User && $user->getLevel() > 0) {
            $url = $this->links->createLoginLink($user, $request)->getUrl() . ($loginLink ? '' : '&reset=1');
            $this->sender->send(
                $email,
                sprintf((string) constant($loginLink ? '_US_LOGINLINK_SUBJECT' : '_US_RESETLINK_SUBJECT'), $site),
                sprintf((string) constant($loginLink ? '_US_LOGINLINK_BODY' : '_US_RESETLINK_BODY'), $user->getUname(), $site, self::MINUTES, $url),
            );

            return;
        }
        if ($user === null && (int) $this->config->get('allow_register', 0) === 1) {
            $this->sender->send(
                $email,
                sprintf((string) constant('_US_REGISTERLINK_SUBJECT'), $site),
                sprintf((string) constant('_US_REGISTERLINK_BODY'), $site, $request->getSchemeAndHttpHost() . $request->getBasePath() . '/register.php'),
            );
        }
    }
}
