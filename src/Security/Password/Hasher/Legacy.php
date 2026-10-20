<?php

declare(strict_types=1);

namespace App\Security\Password\Hasher;

use App\Legacy\LegacyRuntime;
use Icms\Core\Password;
use Icms\Core\Password\Legacy as LegacyFormats;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

/**
 * Verifies the password formats of ImpressCMS before 3.0, plain and wrapped
 * (Icms\Core\Password\Legacy), as the `migrate_from` hasher of
 * App\Security\User\Entity: after a successful login Symfony stores a fresh
 * hash of the configured algorithm. It never hashes.
 */
final class Legacy implements PasswordHasherInterface
{
    public function __construct(private readonly LegacyRuntime $runtime)
    {
    }

    public function hash(#[\SensitiveParameter] string $plainPassword): string
    {
        throw new \LogicException('Legacy password formats are only verified.');
    }

    public function verify(string $hashedPassword, #[\SensitiveParameter] string $plainPassword): bool
    {
        if (!LegacyFormats::isWrapped($hashedPassword) && !LegacyFormats::isUnwrapped($hashedPassword)) {
            return false;
        }
        // the main salt is XOOPS_DB_SALT of mainfile.php
        $this->runtime->bootTo('constants');

        return LegacyFormats::verify($hashedPassword, $plainPassword, Password::hasher(), (string) constant('XOOPS_DB_SALT'));
    }

    public function needsRehash(string $hashedPassword): bool
    {
        return true;
    }
}
