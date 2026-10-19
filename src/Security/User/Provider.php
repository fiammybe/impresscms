<?php

declare(strict_types=1);

namespace App\Security\User;

use App\Legacy\Dbal\Tables;
use Symfony\Component\DependencyInjection\Attribute\AutowireServiceClosure;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * ImpressCMS users from the database, with DBAL: no legacy boot is needed to
 * know who is logged in. Users that are deleted or not active (level < 1)
 * are not found, so their login ends with the next request.
 *
 * @implements UserProviderInterface<Entity>
 */
final class Provider implements UserProviderInterface
{
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
     * @param string $identifier the user name (uname)
     */
    public function loadUserByIdentifier(string $identifier): Entity
    {
        return $this->load('uname = ?', $identifier)
            ?? throw new UserNotFoundException(sprintf('No active ImpressCMS user "%s".', $identifier));
    }

    public function loadUserById(int $id): Entity
    {
        return $this->load('uid = ?', $id)
            ?? throw new UserNotFoundException(sprintf('No active ImpressCMS user with id %d.', $id));
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

    private function load(string $condition, int|string $value): ?Entity
    {
        $row = ($this->connection)()->fetchAssociative(
            'SELECT uid, uname, name FROM ' . $this->tables->name('users') . ' WHERE ' . $condition . ' AND level > 0',
            [$value],
        );
        if ($row === false) {
            return null;
        }

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
        );
    }
}
