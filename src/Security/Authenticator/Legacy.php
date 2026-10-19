<?php

declare(strict_types=1);

namespace App\Security\Authenticator;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * The authenticator of the legacy login (design D5 of symfony-owns-the-session).
 *
 * No request triggers it: ImpressCMS checks the credentials (include/checklogin.php
 * and the auth methods behind it) and logs the user in programmatically through
 * App\Session\Host, which uses this authenticator for
 * Security::login(). It is also the firewall's entry point: anonymous visitors
 * that a route denies go to the ImpressCMS login with the URL to return to.
 */
final class Legacy extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    public function supports(Request $request): ?bool
    {
        return false;
    }

    public function authenticate(Request $request): Passport
    {
        throw new \LogicException('The legacy login authenticates programmatically only.');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return null;
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new RedirectResponse($request->getBasePath() . '/user.php?xoops_redirect=' . rawurlencode($request->getRequestUri()));
    }
}
