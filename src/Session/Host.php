<?php

declare(strict_types=1);

namespace App\Session;

use App\Security\Authenticator\Legacy as LegacyAuthenticator;
use App\Security\User\Entity as User;
use App\Runtime\HostRunner;
use App\Security\User\Provider as Users;
use Icms\Core\Session as LegacySession;
use Icms\Core\Session\IHost;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionUtils;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

/**
 * The host's session and authentication for ImpressCMS (design D3-D5 of
 * symfony-owns-the-session): installed with Icms\Core\Session::setHost() for
 * every main request, so that the session legacy code starts is Symfony's and
 * icms::$user is the user of the security token.
 *
 * Legacy pages run after HttpKernel::handle(), when the request is no longer
 * on the request stack; the host keeps the request it was installed for and
 * puts it back for Security::login().
 */
final class Host implements IHost
{
    private ?Request $request = null;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly Security $security,
        private readonly Users $users,
        private readonly Database\Handler $sessions,
    ) {
    }

    /** After the session listener (128) gave the request its session, before the firewall (8). */
    #[AsEventListener(event: KernelEvents::REQUEST, priority: 100)]
    public function install(RequestEvent $event): void
    {
        if (!$event->isMainRequest() || !interface_exists(IHost::class)) {
            return;
        }
        $this->request = $event->getRequest();
        LegacySession::setHost($this);
    }

    public function start(): void
    {
        $this->request()->getSession()->start();
        $this->withoutPhpSessionCookie();
        if ($this->request()->attributes->get(HostRunner::LEGACY_PAGE) === true) {
            // as when ImpressCMS started the session itself (PHP's "nocache"
            // limiter): legacy pages with a session are never cached. Symfony's
            // responses get their cache headers from the session listener.
            header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Pragma: no-cache');
        }
    }

    public function userId(): int
    {
        $user = $this->token()?->getUser();
        if ($user instanceof User) {
            return $user->getId();
        }

        // A session that ImpressCMS stored itself, before the host owned the
        // session (an upgrade): its login is taken over once. Symfony stamps
        // every session it stores, so later the keys are only a mirror.
        $uid = (int) ($_SESSION['xoopsUserId'] ?? 0);
        if ($uid > 0 && $this->sessionFromBeforeTheHost()) {
            $this->login($uid);
            $user = $this->token()?->getUser();

            return $user instanceof User ? $user->getId() : 0;
        }

        return 0;
    }

    /**
     * The security token. Reading it first runs the lazy firewall's
     * authenticators (remember me), which need the request on the stack: while a
     * legacy script runs, the kernel has already popped it.
     */
    private function token(): ?TokenInterface
    {
        return $this->withRequest(function (): ?TokenInterface {
            $token = $this->tokenStorage->getToken();
            // a remembered login migrates the session
            $this->withoutPhpSessionCookie();

            return $token;
        });
    }

    /**
     * Runs $work with the host's request on Symfony's request stack.
     *
     * @template T
     *
     * @param \Closure(): T $work
     *
     * @return T
     */
    private function withRequest(\Closure $work): mixed
    {
        $request = $this->request();
        $pushed = $this->requestStack->getCurrentRequest() !== $request;
        if ($pushed) {
            $this->requestStack->push($request);
        }
        try {
            return $work();
        } finally {
            if ($pushed) {
                $this->requestStack->pop();
            }
        }
    }

    private function sessionFromBeforeTheHost(): bool
    {
        $session = $this->request()->getSession();

        return $session->isStarted() && $this->sessions->storedBeforeTheHost($session->getId());
    }

    public function login(int $uid): void
    {
        try {
            $user = $this->users->loadUserById($uid);
        } catch (UserNotFoundException) {
            $this->logout();

            return;
        }

        $this->withRequest(function () use ($user): void {
            $this->security->login($user, LegacyAuthenticator::class, 'main');
            $this->withoutPhpSessionCookie();
        });
    }

    public function logout(): void
    {
        if ($this->token()?->getUser() !== null) {
            // Symfony's logout listeners: the remember-me token and cookie go
            $this->withRequest(fn () => $this->security->logout(false));
        }
        $this->tokenStorage->setToken(null);
        $this->request()->getSession()->invalidate();
        $this->withoutPhpSessionCookie();
    }

    /**
     * PHP sends the session cookie whenever it starts or regenerates a session,
     * and the bridge would pass that header on with a legacy page. Symfony's
     * session listener sends the cookie itself when the session is new or its id
     * changed (as it does for Symfony routes), so PHP's header is removed.
     */
    private function withoutPhpSessionCookie(): void
    {
        $session = $this->request()->getSession();
        if ($session->isStarted()) {
            SessionUtils::popSessionCookie($session->getName(), $session->getId());
        }
    }

    private function request(): Request
    {
        return $this->request
            ?? $this->requestStack->getMainRequest()
            ?? throw new \LogicException('The session host has no request.');
    }
}
