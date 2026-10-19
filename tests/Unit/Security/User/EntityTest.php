<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security\User;

use App\Security\User\Entity;
use PHPUnit\Framework\TestCase;

final class EntityTest extends TestCase
{
    public function testWebmasterRoles(): void
    {
        $user = new Entity(1, 'webmaster', 'Webmaster', [1, 2], 1);

        self::assertTrue($user->isWebmaster());
        self::assertSame(['ROLE_USER', 'ROLE_WEBMASTER', 'ROLE_GROUP_1', 'ROLE_GROUP_2'], $user->getRoles());
        self::assertSame('webmaster', $user->getUserIdentifier());
    }

    public function testMemberRoles(): void
    {
        $user = new Entity(2, 'member', 'member', [2], 1);

        self::assertFalse($user->isWebmaster());
        self::assertSame(['ROLE_USER', 'ROLE_GROUP_2'], $user->getRoles());
    }
}
