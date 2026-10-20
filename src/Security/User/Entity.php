<?php

declare(strict_types=1);

namespace App\Security\User;

use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * An ImpressCMS user as the Symfony user: the identity of both applications
 * (App\Security\User\Provider loads it, icms::$user is the same user).
 *
 * Roles: ROLE_USER for every logged-in user, ROLE_WEBMASTER for members of
 * the webmasters group (ICMS_GROUP_ADMIN) and ROLE_GROUP_<id> per group.
 * The identifier is the login name. Equal by id, so that a change of groups
 * does not end the login. The password hash is not kept in the session.
 */
final class Entity implements UserInterface, PasswordAuthenticatedUserInterface, EquatableInterface
{
    /**
     * @param list<int>   $groups
     * @param string|null $password the `pass` column, self-describing
     *                              (Icms\Core\Password\Legacy::describe())
     * @param int         $level    0 not activated, -1 deactivated, 1 and more active
     */
    public function __construct(
        private readonly int $id,
        private readonly string $uname,
        private readonly string $displayName,
        private readonly array $groups,
        private readonly int $webmasterGroup,
        private readonly string $loginName = '',
        private readonly ?string $password = null,
        private readonly int $level = 1,
        private readonly bool $passwordExpired = false,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUname(): string
    {
        return $this->uname;
    }

    public function getLoginName(): string
    {
        return $this->loginName !== '' ? $this->loginName : $this->uname;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    /**
     * @return list<int>
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    public function isWebmaster(): bool
    {
        return in_array($this->webmasterGroup, $this->groups, true);
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function isPasswordExpired(): bool
    {
        return $this->passwordExpired;
    }

    public function getRoles(): array
    {
        $roles = ['ROLE_USER'];
        if ($this->isWebmaster()) {
            $roles[] = 'ROLE_WEBMASTER';
        }
        foreach ($this->groups as $group) {
            $roles[] = 'ROLE_GROUP_' . $group;
        }

        return $roles;
    }

    public function getUserIdentifier(): string
    {
        return $this->getLoginName();
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function isEqualTo(UserInterface $user): bool
    {
        return $user instanceof self && $user->id === $this->id;
    }

    /**
     * Without the password hash: the token in the session needs none, and the
     * user is reloaded on every request.
     *
     * @return array<string, mixed>
     */
    public function __serialize(): array
    {
        return ['password' => null] + get_object_vars($this);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function __unserialize(array $data): void
    {
        foreach ($data as $property => $value) {
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        }
    }

    public function eraseCredentials(): void
    {
    }
}
