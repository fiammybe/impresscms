<?php

declare(strict_types=1);

namespace App\Security\Login;

use App\Legacy\LegacyRuntime;
use App\Security\User\Checker;
use App\Security\User\Entity as User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

/**
 * The response to every login (OpenSpec change remember-me-and-email-links,
 * design D4): the password form and the login link. After a login it does
 * what ImpressCMS did (Icms\Core\Session::loggedIn()) and redirects as the
 * legacy login did, with the message in $_SESSION['redirect_message']: to the
 * new-password form after a reset link, to the reset form for an expired
 * password, else to the return URL within the site.
 */
final class Responder implements AuthenticationSuccessHandlerInterface, AuthenticationFailureHandlerInterface
{
    /** Session key: the time a reset link logged the user in */
    public const PASSWORD_RESET = 'icms_password_reset';

    public function __construct(private readonly LegacyRuntime $runtime)
    {
    }

    /**
     * Whether a form was posted from this site: by Origin, else by Referer.
     * Without either header (some privacy settings drop them) it is accepted.
     */
    public static function sameSite(Request $request): bool
    {
        $source = $request->headers->get('Origin') ?? $request->headers->get('Referer');
        if ($source === null || $source === '' || $source === 'null') {
            return true;
        }
        $host = parse_url($source, PHP_URL_HOST);

        return is_string($host) && strcasecmp($host, $request->getHost()) === 0;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        // the legacy services see the new token: icms::$user is the user
        $this->runtime->bootTo('language');
        \icms_loadLanguageFile('core', 'user');
        if (is_object(\icms::$user)) {
            \icms::$session->loggedIn(\icms::$user);
        }

        $user = $token->getUser();
        $name = $user instanceof User ? $user->getUname() : $token->getUserIdentifier();
        $welcome = sprintf((string) constant('_US_LOGGINGU'), htmlspecialchars($name, ENT_QUOTES));
        if ($request->query->get('reset') === '1') {
            $request->getSession()->set(self::PASSWORD_RESET, time());

            return $this->redirect($request, $request->getBasePath() . '/auth/password', $welcome);
        }
        if ($user instanceof User && $user->isPasswordExpired()) {
            return $this->redirect($request, $request->getBasePath() . '/user.php?op=resetpass', (string) constant('_US_PASSEXPIRED'));
        }

        return $this->redirect($request, $this->target($request), $welcome);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $this->runtime->bootTo('language');
        \icms_loadLanguageFile('core', 'user');

        $message = match (true) {
            $exception instanceof TooManyLoginAttemptsAuthenticationException => (string) constant('_US_LOGINTHROTTLED'),
            $exception instanceof CustomUserMessageAccountStatusException => match ($exception->getMessageKey()) {
                Checker::NOT_ACTIVATED => (string) constant('_US_NOACTTPADM'),
                Checker::ALREADY_ONLINE => (string) constant('_US_MULTLOGIN'),
                default => (string) constant('_NOPERM'),
            },
            // an expired, used or tampered login link
            $request->attributes->get('_route') === 'icms_auth_link_check' => (string) constant('_US_LINKINVALID'),
            default => (string) constant('_US_INCORRECTLOGIN'),
        };
        $target = $request->getBasePath() . '/user.php';
        $return = (string) ($request->request->get('xoops_redirect') ?? $request->query->get('xoops_redirect') ?? '');
        if ($return !== '') {
            $target .= '?xoops_redirect=' . rawurlencode($return);
        }

        return $this->redirect($request, $target, $message);
    }

    /**
     * A redirect with a message for the next page (header.php shows it).
     */
    public function redirect(Request $request, string $url, string $message): RedirectResponse
    {
        // one line, as header.php puts it into a script
        $request->getSession()->set('redirect_message', str_replace(["\r", "\n"], ' ', $message));

        return new RedirectResponse($url);
    }

    /**
     * The page to return to: xoops_redirect when it stays within the site and
     * is not the registration, else the home page (as checklogin.php did).
     */
    private function target(Request $request): string
    {
        $home = $request->getSchemeAndHttpHost() . $request->getBasePath() . '/';
        $target = (string) ($request->request->get('xoops_redirect') ?? $request->query->get('xoops_redirect') ?? '');
        if ($target === '' || str_contains($target, 'register')) {
            return $home;
        }
        if (str_starts_with($target, '/') && !str_starts_with($target, '//')) {
            return $request->getSchemeAndHttpHost() . $target;
        }
        $parts = parse_url($target);
        if (is_array($parts) && in_array($parts['scheme'] ?? '', ['http', 'https'], true)
            && strcasecmp($parts['host'] ?? '', $request->getHost()) === 0) {
            return $target;
        }

        return $home;
    }
}
