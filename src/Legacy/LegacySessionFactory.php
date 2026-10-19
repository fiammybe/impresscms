<?php

declare(strict_types=1);

namespace App\Legacy;

use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionFactoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageFactoryInterface;

/**
 * The host's session, with attributes that are the $_SESSION entries legacy
 * code uses (LegacySessionAttributeBag). The storage is
 * App\Session\IcmsSessionStorageFactory; legacy code reaches the session
 * through App\Security\LegacySessionHost.
 */
#[AsDecorator('session.factory')]
final class LegacySessionFactory implements SessionFactoryInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        #[Autowire(service: 'session.storage.factory')]
        private readonly SessionStorageFactoryInterface $storageFactory,
        #[Autowire(service: 'session_listener')]
        private readonly ?object $sessionListener = null,
    ) {
    }

    public function createSession(): SessionInterface
    {
        $usageReporter = $this->sessionListener !== null && method_exists($this->sessionListener, 'onSessionUsage')
            ? $this->sessionListener->onSessionUsage(...)
            : null;

        return new Session(
            $this->storageFactory->createStorage($this->requestStack->getMainRequest()),
            new LegacySessionAttributeBag(),
            null,
            $usageReporter,
        );
    }
}
