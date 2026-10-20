<?php

declare(strict_types=1);

namespace App\Security\Authenticator;

use App\Legacy\Dbal\Config;
use App\Legacy\LegacyRuntime;
use App\Security\User\Checker;
use App\Security\User\Entity as User;
use App\Security\User\Provider as Users;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\PasswordUpgradeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * The password login of ImpressCMS (OpenSpec change symfony-password-login,
 * design D5): the legacy login form (POST /user.php with op=login) and the
 * closed-site login form (xoops_login), with their field names uname, pass
 * and xoops_redirect.
 *
 * With the `xoops` authentication method the password hashers verify the
 * password (legacy formats included, upgraded after the login); with `ldap`
 * or `ads` the legacy class authenticates and provisions the user. Responses
 * are the legacy ones: a redirect with the message in
 * $_SESSION['redirect_message'].
 */
final class Password extends AbstractAuthenticator
{
    public function __construct(
        private readonly Users $users,
        private readonly Config $config,
        private readonly LegacyRuntime $runtime,
    ) {
    }

    public function supports(Request $request): bool
    {
        if (!$request->isMethod('POST')) {
            return false;
        }

        return ($request->getPathInfo() === '/user.php' && $request->request->get('op') === 'login')
            || $request->request->has('xoops_login');
    }

    public function authenticate(Request $request): Passport
    {
        if (!self::sameSite($request)) {
            throw new CustomUserMessageAuthenticationException('cross_site');
        }
        $uname = trim((string) $request->request->get('uname', ''));
        $pass = trim((string) $request->request->get('pass', ''));
        if ($uname === '' || $pass === '') {
            throw new BadCredentialsException('Empty login name or password.');
        }

        if ($this->usesLegacyMethod($uname)) {
            return $this->legacyPassport($uname, $pass);
        }

        return new Passport(
            new UserBadge($uname, $this->users->loadUserByIdentifier(...)),
            new PasswordCredentials($pass),
            [new PasswordUpgradeBadge($pass, $this->users)],
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): Response
    {
        // the legacy services see the new token: icms::$user is the user
        $this->runtime->bootTo('language');
        \icms_loadLanguageFile('core', 'user');
        if (is_object(\icms::$user)) {
            \icms::$session->loggedIn(\icms::$user);
        }

        $user = $token->getUser();
        if ($user instanceof User && $user->isPasswordExpired()) {
            return $this->redirect($request, $request->getBasePath() . '/user.php?op=resetpass', (string) constant('_US_PASSEXPIRED'));
        }
        $name = $user instanceof User ? $user->getUname() : $token->getUserIdentifier();

        return $this->redirect($request, $this->target($request), sprintf((string) constant('_US_LOGGINGU'), htmlspecialchars($name, ENT_QUOTES)));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $this->runtime->bootTo('language');
        \icms_loadLanguageFile('core', 'user');

        $message = match (true) {
            $exception instanceof TooManyLoginAttemptsAuthenticationException => defined('_US_LOGINTHROTTLED')
                ? (string) constant('_US_LOGINTHROTTLED')
                : 'Too many failed login attempts. Please try again in a minute.',
            $exception instanceof CustomUserMessageAccountStatusException => match ($exception->getMessageKey()) {
                Checker::NOT_ACTIVATED => (string) constant('_US_NOACTTPADM'),
                Checker::ALREADY_ONLINE => (string) constant('_US_MULTLOGIN'),
                default => (string) constant('_NOPERM'),
            },
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
     * Whether the form was posted from this site: by Origin, else by Referer.
     * Without either header (some privacy settings drop them) it is accepted.
     */
    private static function sameSite(Request $request): bool
    {
        $source = $request->headers->get('Origin') ?? $request->headers->get('Referer');
        if ($source === null || $source === '' || $source === 'null') {
            return true;
        }
        $host = parse_url($source, PHP_URL_HOST);

        return is_string($host) && strcasecmp($host, $request->getHost()) === 0;
    }

    /**
     * The `ldap` or `ads` authentication method applies (the webmasters in
     * ldap_users_bypass always use the `xoops` method).
     */
    private function usesLegacyMethod(string $uname): bool
    {
        $method = (string) $this->config->get('auth_method', 'xoops');
        if ($method === '' || $method === 'xoops') {
            return false;
        }

        return !in_array($uname, (array) $this->config->get('ldap_users_bypass', []), true);
    }

    /**
     * The legacy LDAP/ADS classes check the credentials and provision the user;
     * the user is then loaded like any other.
     */
    private function legacyPassport(string $uname, string $pass): Passport
    {
        $this->runtime->bootTo('services');
        \icms_loadLanguageFile('core', 'auth');
        $auth = \Icms\Auth\Factory::getAuthConnection(\Icms\Core\DataFilter::addSlashes($uname));
        $legacyUser = $auth->authenticate(addslashes($uname), $pass);
        if (!is_object($legacyUser)) {
            throw new BadCredentialsException('The ImpressCMS authentication method refused the credentials.');
        }

        return new SelfValidatingPassport(new UserBadge((string) $legacyUser->getVar('login_name', 'n'), $this->users->loadUserByIdentifier(...)));
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

    private function redirect(Request $request, string $url, string $message): RedirectResponse
    {
        // shown by header.php on the next page; one line, as it goes into a script
        $request->getSession()->set('redirect_message', str_replace(["\r", "\n"], ' ', $message));

        return new RedirectResponse($url);
    }
}
