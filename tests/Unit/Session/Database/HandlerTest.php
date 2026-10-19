<?php

declare(strict_types=1);

namespace App\Tests\Unit\Session\Database;

use App\Session\Database\Handler;
use PHPUnit\Framework\TestCase;

/**
 * The IP binding of the session table, as Icms\Core\Session had it: the first
 * three IPv4 blocks or the first seven IPv6 groups must match.
 */
final class HandlerTest extends TestCase
{
    public function testIpv4SessionsAreBoundToTheFirstThreeBlocks(): void
    {
        self::assertTrue(Handler::sameNetwork('192.168.10.20', '192.168.10.99'));
        self::assertFalse(Handler::sameNetwork('192.168.10.20', '192.168.11.20'));
        self::assertFalse(Handler::sameNetwork('192.168.10.20', ''));
    }

    public function testIpv6SessionsAreBoundToTheFirstSevenGroups(): void
    {
        self::assertTrue(Handler::sameNetwork('2001:db8:0:0:0:0:1:1', '2001:db8::1:ffff'));
        self::assertFalse(Handler::sameNetwork('2001:db8::1:1', '2001:db8::2:1'));
        self::assertTrue(Handler::sameNetwork('::1', '::1'));
    }

    public function testSessionsWithoutAnAddressAreNotBound(): void
    {
        self::assertTrue(Handler::sameNetwork('', '10.0.0.1'));
    }
}
