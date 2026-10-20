<?php

declare(strict_types=1);

namespace App\Security\User;

use App\Legacy\Dbal\Config;
use App\Legacy\Dbal\Tables;
use Symfony\Component\DependencyInjection\Attribute\AutowireServiceClosure;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * The account checks of the ImpressCMS login (formerly include/checklogin.php):
 * accounts that are not activated or deactivated, users outside the allowed
 * groups of a closed site, and users who are already online when multiple
 * logins are not allowed. The message keys are translated by the login
 * (App\Security\Authenticator\Password).
 */
final class Checker implements UserCheckerInterface
{
    public const NOT_ACTIVATED = 'not_activated';
    public const SITE_CLOSED = 'site_closed';
    public const ALREADY_ONLINE = 'already_online';

    /** The online table keeps visitors for five minutes (checklogin.php's gc(300)). */
    private const ONLINE_SECONDS = 300;

    /**
     * @param \Closure(): \Doctrine\DBAL\Connection $connection created on first use: the
     *                                                  database settings come from mainfile.php
     */
    public function __construct(
        private readonly Config $config,
        private readonly Tables $tables,
        #[AutowireServiceClosure('doctrine.dbal.default_connection')]
        private readonly \Closure $connection,
    ) {
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if ($user instanceof Entity && $user->getLevel() < 1) {
            throw new CustomUserMessageAccountStatusException(self::NOT_ACTIVATED);
        }
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
        if (!$user instanceof Entity) {
            return;
        }

        if ((int) $this->config->get('closesite', 0) === 1 && !$user->isWebmaster()) {
            $allowed = array_map('intval', (array) $this->config->get('closesite_okgrp', []));
            if (array_intersect($allowed, $user->getGroups()) === []) {
                throw new CustomUserMessageAccountStatusException(self::SITE_CLOSED);
            }
        }

        if ((int) $this->config->get('multi_login', 0) === 1) {
            $online = ($this->connection)()->fetchOne(
                'SELECT COUNT(*) FROM ' . $this->tables->name('online') . ' WHERE online_uid = ? AND online_updated > ?',
                [$user->getId(), time() - self::ONLINE_SECONDS],
            );
            if ((int) $online > 0) {
                throw new CustomUserMessageAccountStatusException(self::ALREADY_ONLINE);
            }
        }
    }
}
