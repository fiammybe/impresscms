<?php
/**
 * The password formats of ImpressCMS before 3.0, verify-only, and their
 * wrapped form
 *
 * Formats (`XOOPS_DB_SALT` is the main salt):
 * - stored: `$<enc>$<iterations>$<salt>-<hash>`, enc 21-40;
 * - `$20$20$<md5>`;
 * - salt column: the hash alone, with the user's `salt` and `enc_type`
 *   columns (enc 0 is plain md5, 1-16 a salted hash). describe() turns it
 *   into `$icms-column$<enc>$<salt>$<hash>`, so that every legacy value
 *   describes itself.
 *
 * wrap() replaces a legacy value by `$icms-legacy$<scheme>.<enc>.<iterations>.<salt>`
 * followed by a modern hash of the legacy value (the digest); verify()
 * recomputes the digest from the password and checks it against that hash.
 * No legacy hash then remains in the database.
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		Core
 * @subpackage	Password
 * @since		3.0
 */

namespace Icms\Core\Password;

use Symfony\Component\PasswordHasher\PasswordHasherInterface;

final class Legacy {

	public const WRAPPED = '$icms-legacy$';
	public const COLUMN = '$icms-column$';

	/** Algorithms of the salt-column format */
	private const COLUMN_ALGORITHMS = [
		1 => 'sha256', 2 => 'sha384', 3 => 'sha512', 4 => 'ripemd128', 5 => 'ripemd160', 6 => 'whirlpool',
		7 => 'haval128,4', 8 => 'haval160,4', 9 => 'haval192,4', 10 => 'haval224,4', 11 => 'haval256,4',
		12 => 'haval128,5', 13 => 'haval160,5', 14 => 'haval192,5', 15 => 'haval224,5', 16 => 'haval256,5',
	];

	/** Algorithms of the stored format */
	private const STORED_ALGORITHMS = [
		21 => 'sha256', 22 => 'sha384', 23 => 'sha512', 24 => 'ripemd128', 25 => 'ripemd160', 26 => 'whirlpool',
		27 => 'haval128,4', 28 => 'haval160,4', 29 => 'haval192,4', 30 => 'haval224,4', 31 => 'haval256,4',
		32 => 'haval128,5', 33 => 'haval160,5', 34 => 'haval192,5', 35 => 'haval224,5', 36 => 'haval256,5',
		37 => 'ripemd256', 38 => 'ripemd320', 39 => 'snefru256', 40 => 'gost',
	];

	private const STORED_PATTERN = '/^\$(\d+)\$(\d+)\$([a-z0-9_]*)-([a-z0-9_]*)$/i';

	/**
	 * The self-describing form of a value of the `pass` column: unchanged for
	 * stored, `$20$`, wrapped and modern values; with the salt and type for
	 * values of the salt-column format.
	 */
	public static function describe(string $pass, string $saltColumn, int $encTypeColumn): string {
		if ($pass === '' || $pass[0] === '$') {
			return $pass;
		}
		return self::COLUMN . $encTypeColumn . '$' . $saltColumn . '$' . $pass;
	}

	/**
	 * Whether a self-describing value is a legacy one that is not wrapped yet.
	 */
	public static function isUnwrapped(string $value): bool {
		return self::parse($value) !== null;
	}

	public static function isWrapped(string $value): bool {
		return str_starts_with($value, self::WRAPPED);
	}

	/**
	 * Replaces a self-describing legacy value by its wrapped form.
	 *
	 * @throws \InvalidArgumentException for a value that is not a legacy one
	 */
	public static function wrap(string $value, PasswordHasherInterface $hasher): string {
		$parsed = self::parse($value);
		if ($parsed === null) {
			throw new \InvalidArgumentException('Not a legacy password value.');
		}
		[$scheme, $enc, $iterations, $salt, $digest] = $parsed;

		return self::WRAPPED . $scheme . '.' . $enc . '.' . $iterations . '.' . $salt . $hasher->hash($digest);
	}

	/**
	 * Whether $plain is the password of a wrapped or self-describing legacy value.
	 */
	public static function verify(string $value, string $plain, PasswordHasherInterface $hasher, string $mainSalt): bool {
		if (self::isWrapped($value)) {
			$rest = substr($value, strlen(self::WRAPPED));
			$end = strpos($rest, '$');
			if ($end === false) {
				return false;
			}
			$parameters = explode('.', substr($rest, 0, $end), 4);
			if (count($parameters) !== 4) {
				return false;
			}
			[$scheme, $enc, $iterations, $salt] = $parameters;
			$digest = self::digest($plain, $scheme, (int) $enc, (int) $iterations, $salt, $mainSalt);

			return $digest !== null && $hasher->verify(substr($rest, $end), $digest);
		}

		$parsed = self::parse($value);
		if ($parsed === null) {
			return false;
		}
		[$scheme, $enc, $iterations, $salt, $stored] = $parsed;
		$digest = self::digest($plain, $scheme, $enc, $iterations, $salt, $mainSalt);

		return $digest !== null && hash_equals($stored, $digest);
	}

	/**
	 * The legacy value ImpressCMS computed for $plain: `c` salt column, `m` `$20$`, `s` stored.
	 */
	public static function digest(string $plain, string $scheme, int $enc, int $iterations, string $salt, string $mainSalt): ?string {
		switch ($scheme) {
			case 'c':
				if ($enc === 0) {
					return md5($plain);
				}
				return isset(self::COLUMN_ALGORITHMS[$enc]) ? hash(self::COLUMN_ALGORITHMS[$enc], $salt . md5($plain) . $mainSalt) : null;
			case 'm':
				return '$20$20$' . md5($plain);
			case 's':
				if (!isset(self::STORED_ALGORITHMS[$enc])) {
					return null;
				}
				return '$' . $enc . '$' . $iterations . '$' . $salt . '-' . self::rehash(
					self::rehash($salt, $iterations, 21) . self::rehash($plain, $iterations, 21) . self::rehash($mainSalt, $iterations, 21),
					$iterations,
					$enc
				);
			default:
				return null;
		}
	}

	/**
	 * [scheme, enc, iterations, salt, value] of an unwrapped legacy value, or null.
	 *
	 * @return array{0: string, 1: int, 2: int, 3: string, 4: string}|null
	 */
	private static function parse(string $value): ?array {
		if (str_starts_with($value, self::COLUMN)) {
			$parts = explode('$', substr($value, strlen(self::COLUMN)), 3);
			return count($parts) === 3 && ctype_digit($parts[0]) ? ['c', (int) $parts[0], 0, $parts[1], $parts[2]] : null;
		}
		if (str_starts_with($value, '$20$20$')) {
			return ['m', 20, 20, '', $value];
		}
		if (preg_match(self::STORED_PATTERN, $value, $m)) {
			return ['s', (int) $m[1], (int) $m[2], $m[3], $value];
		}
		return null;
	}

	/**
	 * ImpressCMS' "stretching": hashes the doubled input once per iteration,
	 * always from the same input (so in effect once), and yields '' for no
	 * iteration.
	 */
	private static function rehash(string $hash, int $iterations, int $enc): string {
		return $iterations > 0 ? hash(self::STORED_ALGORITHMS[$enc], $hash . $hash) : '';
	}
}
