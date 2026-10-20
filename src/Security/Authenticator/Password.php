<?php

declare(strict_types=1);

namespace App\Security\Authenticator;

use App\Legacy\Dbal\Config;
use App\Legacy\LegacyRuntime;
use App\Security\Login\Responder;
use App\Security\User\Provider as Users;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\PasswordUpgradeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * The password login of ImpressCMS (OpenSpec change symfony-password-login,
 * design D5): the legacy login form (POST /user.php with op=login) and the
 * closed-site login form (xoops_login), with their field names uname, pass,
 * rememberme and xoops_redirect.
 *
 * With the `xoops` authentication method the password hashers verify the
 * password (legacy formats included, upgraded after the login); with `ldap`
 * or `ads` the legacy class authenticates and provisions the user. The
 * response is App\Security\Login\Responder's. "Remember me" applies when the
 * preference allows it (remember-me-and-email-links, design D1).
 */
final class Password extends AbstractAuthenticator
{
    public function __construct(
        private readonly Users $users,
        private readonly Config $config,
        private readonly LegacyRuntime $runtime,
        private readonly Responder $responder,
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
        if (!Responder::sameSite($request)) {
            throw new CustomUserMessageAuthenticationException('cross_site');
        }
        $uname = trim((string) $request->request->get('uname', ''));
        $pass = trim((string) $request->request->get('pass', ''));
        if ($uname === '' || $pass === '') {
            throw new BadCredentialsException('Empty login name or password.');
        }

        $badges = [];
        if ((int) $this->config->get('remember_me', 0) === 1 && $request->request->has('rememberme')) {
            $badges[] = (new RememberMeBadge())->enable();
        }

        if ($this->usesLegacyMethod($uname)) {
            $passport = $this->legacyPassport($uname, $pass);
            foreach ($badges as $badge) {
                $passport->addBadge($badge);
            }

            return $passport;
        }

        return new Passport(
            new UserBadge($uname, $this->users->loadUserByIdentifier(...)),
            new PasswordCredentials($pass),
            [new PasswordUpgradeBadge($pass, $this->users), ...$badges],
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): Response
    {
        return $this->responder->onAuthenticationSuccess($request, $token);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return $this->responder->onAuthenticationFailure($request, $exception);
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
}
