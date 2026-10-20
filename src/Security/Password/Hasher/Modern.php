<?php

declare(strict_types=1);

namespace App\Security\Password\Hasher;

use Icms\Core\Password;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

/**
 * The password hasher of App\Security\User\Entity: the one legacy code uses
 * too (Icms\Core\Password::hasher(), Argon2id where PHP offers it, bcrypt
 * otherwise), so that the host and ImpressCMS produce the same hashes.
 */
final class Modern implements PasswordHasherInterface
{
    public function hash(#[\SensitiveParameter] string $plainPassword): string
    {
        return Password::hasher()->hash($plainPassword);
    }

    public function verify(string $hashedPassword, #[\SensitiveParameter] string $plainPassword): bool
    {
        return Password::hasher()->verify($hashedPassword, $plainPassword);
    }

    public function needsRehash(string $hashedPassword): bool
    {
        return Password::hasher()->needsRehash($hashedPassword);
    }
}
