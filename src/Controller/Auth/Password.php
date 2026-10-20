<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Front\Page;
use App\Legacy\Dbal\Config;
use App\Legacy\LegacyRuntime;
use App\Security\Login\Responder;
use App\Security\RememberMe\TokenProvider;
use App\Security\User\Entity as User;
use App\Security\User\Provider as Users;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Twig\Environment;

/**
 * The new password after a reset link (OpenSpec change
 * remember-me-and-email-links, design D3): for 15 minutes after the link
 * logged the user in. Saving stores a hash of the shared hasher, clears an
 * expired password and forgets the user's remembered devices.
 */
final class Password
{
    /** How long after the reset link the form may be used */
    private const SECONDS = 900;

    public function __construct(
        private readonly Page $page,
        private readonly Environment $twig,
        private readonly Users $users,
        private readonly TokenProvider $rememberMe,
        private readonly Config $config,
        private readonly LegacyRuntime $runtime,
        private readonly Responder $responder,
        private readonly CsrfTokenManagerInterface $csrf,
    ) {
    }

    #[Route('/auth/password', name: 'icms_auth_password', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, #[CurrentUser] ?User $user): Response
    {
        $session = $request->getSession();
        $since = (int) $session->get(Responder::PASSWORD_RESET, 0);
        if ($user === null || $since < time() - self::SECONDS) {
            $session->remove(Responder::PASSWORD_RESET);

            return $this->responder->redirect($request, $request->getBasePath() . '/user.php', $this->text('_US_LINKINVALID'));
        }

        // [language constant, sprintf arguments] of the error to show
        $error = null;
        $minimum = max(1, (int) $this->config->get('minpass', 0));
        if ($request->isMethod('POST')) {
            $password = (string) $request->request->get('password', '');
            if (!$this->csrf->isTokenValid(new CsrfToken('icms_new_password', (string) $request->request->get('_token')))) {
                $error = ['_NOPERM', []];
            } elseif ($password !== (string) $request->request->get('password2', '')) {
                $error = ['_US_PASSNOTSAME', []];
            } elseif (mb_strlen($password) < $minimum) {
                $error = ['_US_PWDTOOSHORT', [$minimum]];
            } else {
                $this->users->changePassword($user, $password);
                $this->rememberMe->deleteTokensOf($user->getUserIdentifier());
                $session->remove(Responder::PASSWORD_RESET);

                return $this->responder->redirect($request, $request->getBasePath() . '/user.php', $this->text('_US_NEWPASSWORD_SAVED'));
            }
        }

        $action = $request->getRequestUri();
        // now: the request (and its session) has left the stack when the page renders
        $token = $this->csrf->getToken('icms_new_password')->getValue();

        // the language constants exist once host_page.php booted the page
        return $this->page->render(function () use ($error, $action, $token): array {
            $title = (string) constant('_US_NEWPASSWORD_TITLE');

            return ['title' => $title, 'html' => $this->twig->render('auth/password.html.twig', [
                'title' => $title,
                'error' => $error === null ? null : sprintf((string) constant($error[0]), ...$error[1]),
                'action' => $action,
                'token' => $token,
                'label_password' => (string) constant('_US_NEWPASSWORD'),
                'label_repeat' => (string) constant('_US_VERIFYPASS'),
                'button' => (string) constant('_US_SAVECHANGES'),
            ])];
        });
    }

    /**
     * A user language string for a redirect message. Only on the way to a
     * redirect: booting legacy here breaks a page rendered afterwards
     * (App\Front\Page).
     */
    private function text(string $constant): string
    {
        $this->runtime->bootTo('language');
        \icms_loadLanguageFile('core', 'user');

        return (string) constant($constant);
    }
}
