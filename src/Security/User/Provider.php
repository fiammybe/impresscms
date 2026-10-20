<?php

declare(strict_types=1);

namespace App\Security\User;

use App\Legacy\Dbal\Tables;
use Icms\Core\Password;
use Icms\Core\Password\Legacy;
use Symfony\Component\DependencyInjection\Attribute\AutowireServiceClosure;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * ImpressCMS users from the database, with DBAL: no legacy boot is needed to
 * know who is logged in. A login name with `@` is an e-mail address, as in
 * Icms\Member\Handler::loginUser(). The login accepts any account state and
 * leaves the checks to App\Security\User\Checker; a logged-in user who is
 * deleted or no longer active (level < 1) is dropped with the next request.
 * After a login with a legacy password, Symfony stores a fresh hash here.
 *
 * @implements UserProviderInterface<Entity>
 */
final class Provider implements UserProviderInterface, PasswordUpgraderInterface
{
    private const COLUMNS = 'uid, uname, login_name, name, level, pass, salt, enc_type, pass_expired';

    /**
     * @param \Closure(): \Doctrine\DBAL\Connection $connection created on first use: the
     *                                                  database settings come from mainfile.php
     */
    public function __construct(
        #[AutowireServiceClosure('doctrine.dbal.default_connection')]
        private readonly \Closure $connection,
        private readonly Tables $tables,
    ) {
    }

    /**
     * @param string $identifier the login name, or the e-mail address
     */
    public function loadUserByIdentifier(string $identifier): Entity
    {
        return $this->load(str_contains($identifier, '@') ? 'email = ?' : 'login_name = ?', $identifier)
            ?? throw new UserNotFoundException(sprintf('No ImpressCMS user "%s".', $identifier));
    }

    /**
     * An active user (level > 0) by id.
     */
    public function loadUserById(int $id): Entity
    {
        $user = $this->load('uid = ?', $id);
        if ($user === null || $user->getLevel() < 1) {
            throw new UserNotFoundException(sprintf('No active ImpressCMS user with id %d.', $id));
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): Entity
    {
        if (!$user instanceof Entity) {
            throw new UnsupportedUserException(sprintf('Unsupported user "%s".', $user::class));
        }

        return $this->loadUserById($user->getId());
    }

    public function supportsClass(string $class): bool
    {
        return $class === Entity::class;
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Entity) {
            throw new UnsupportedUserException(sprintf('Unsupported user "%s".', $user::class));
        }
        ($this->connection)()->executeStatement(
            'UPDATE ' . $this->tables->name('users') . ' SET pass = ? WHERE uid = ?',
            [$newHashedPassword, $user->getId()],
        );
    }

    /**
     * Sets a new password (the shared hasher of Icms\Core\Password) and clears
     * an expired one.
     */
    public function changePassword(Entity $user, #[\SensitiveParameter] string $plainPassword): void
    {
        ($this->connection)()->executeStatement(
            'UPDATE ' . $this->tables->name('users') . ' SET pass = ?, pass_expired = 0 WHERE uid = ?',
            [Password::hasher()->hash($plainPassword), $user->getId()],
        );
    }

    private function load(string $condition, int|string $value): ?Entity
    {
        $rows = ($this->connection)()->fetchAllAssociative(
            'SELECT ' . self::COLUMNS . ' FROM ' . $this->tables->name('users') . ' WHERE ' . $condition,
            [$value],
        );
        if (count($rows) !== 1) {
            // none, or an e-mail address that several accounts share
            return null;
        }
        $row = $rows[0];

        $groups = array_map('intval', ($this->connection)()->fetchFirstColumn(
            'SELECT groupid FROM ' . $this->tables->name('groups_users_link') . ' WHERE uid = ? ORDER BY groupid',
            [(int) $row['uid']],
        ));
        $uname = (string) $row['uname'];
        $name = trim((string) $row['name']);

        return new Entity(
            (int) $row['uid'],
            $uname,
            $name !== '' ? $name : $uname,
            array_values(array_unique($groups)),
            defined('ICMS_GROUP_ADMIN') ? (int) constant('ICMS_GROUP_ADMIN') : 1,
            (string) $row['login_name'],
            Legacy::describe((string) $row['pass'], (string) $row['salt'], (int) $row['enc_type']),
            (int) $row['level'],
            (int) $row['pass_expired'] === 1,
        );
    }
}
