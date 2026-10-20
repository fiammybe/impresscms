<?php
/**
 * Icms\Core\Password\Legacy against vectors that the ImpressCMS 2.x code of
 * Icms\Core\Password produced (tests/Fixtures/passwords/legacy-vectors.json):
 * every legacy format verifies, plain and wrapped.
 *
 * @package icms\tests
 */

declare(strict_types=1);

namespace Icms\Tests\Unit\Core\Password;

use Icms\Core\Password\Legacy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

#[Group('spec:authentication')]
final class LegacyTest extends TestCase
{
    /**
     * @return array{main_salt: string, password: string, vectors: list<array{scheme: string, enc_type: int, salt: string, hash: string}>}
     */
    private static function fixture(): array
    {
        return json_decode((string) file_get_contents(dirname(__DIR__, 5) . '/tests/Fixtures/passwords/legacy-vectors.json'), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function vectors(): iterable
    {
        foreach (self::fixture()['vectors'] as $i => $vector) {
            $value = $vector['scheme'] === 'column'
                ? Legacy::describe($vector['hash'], $vector['salt'], $vector['enc_type'])
                : $vector['hash'];
            yield sprintf('#%d %s enc_type %d', $i, $vector['scheme'], $vector['enc_type']) => [$value];
        }
    }

    private static function hasher(): PasswordHasherInterface
    {
        // cheap parameters: the test is about the formats, not the cost
        return new NativePasswordHasher(null, null, 4, PASSWORD_BCRYPT);
    }

    /**
     * @scenario Every legacy format verifies
     */
    #[DataProvider('vectors')]
    public function testTheLegacyValueAcceptsOnlyItsPassword(string $value): void
    {
        ['main_salt' => $main, 'password' => $password] = self::fixture();

        self::assertTrue(Legacy::isUnwrapped($value));
        self::assertTrue(Legacy::verify($value, $password, self::hasher(), $main));
        self::assertFalse(Legacy::verify($value, $password . 'x', self::hasher(), $main));
        self::assertFalse(Legacy::verify($value, substr($password, 0, 32), self::hasher(), $main), 'no truncation to 32 characters');
        $plainMd5 = str_starts_with($value, Legacy::COLUMN . '0$') || str_starts_with($value, '$20$');
        if (!$plainMd5) {
            self::assertFalse(Legacy::verify($value, $password, self::hasher(), $main . 'x'), 'the main salt is part of it');
        }
    }

    /**
     * @scenario Every legacy format verifies
     */
    #[DataProvider('vectors')]
    public function testTheWrappedValueAcceptsOnlyItsPassword(string $value): void
    {
        ['main_salt' => $main, 'password' => $password] = self::fixture();

        $wrapped = Legacy::wrap($value, self::hasher());

        self::assertStringStartsWith(Legacy::WRAPPED, $wrapped);
        self::assertTrue(Legacy::isWrapped($wrapped));
        self::assertFalse(Legacy::isUnwrapped($wrapped));
        self::assertStringNotContainsString(substr($value, -16), $wrapped, 'the legacy hash itself is gone');
        self::assertLessThanOrEqual(255, strlen(Legacy::wrap($value, new NativePasswordHasher())), 'fits the pass column with Argon2id');
        self::assertTrue(Legacy::verify($wrapped, $password, self::hasher(), $main));
        self::assertFalse(Legacy::verify($wrapped, $password . 'x', self::hasher(), $main));
    }

    public function testModernAndEmptyValuesAreNotLegacy(): void
    {
        $modern = self::hasher()->hash('secret');

        self::assertSame($modern, Legacy::describe($modern, 'salt', 3));
        self::assertFalse(Legacy::isUnwrapped($modern));
        self::assertFalse(Legacy::isWrapped($modern));
        self::assertSame('', Legacy::describe('', 'salt', 3));
        self::assertFalse(Legacy::verify('', 'secret', self::hasher(), 'main'));
    }
}
