<?php
/**
 * Wraps the legacy password hashes of the users table (Icms\Core\Password\Legacy)
 *
 * Used by the system module update (step 51) within a time budget, and by
 * the host's `bin/console icms:passwords:wrap` without one. Works on any
 * PDO connection to the ImpressCMS database.
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		Core
 * @subpackage	Password
 * @since		3.0
 */

namespace Icms\Core\Password;

use Icms\Core\Password;

final class Wrapper {

	/**
	 * Legacy values: the salt-column format (no `$`), `$20$` and the stored
	 * format (`$<enc>$...`). Modern hashes, wrapped values and markers such as
	 * `$icms-external$` start with `$` and a letter.
	 */
	private const PENDING = "((pass <> '' AND pass NOT LIKE '$%') OR pass REGEXP '^[$][0-9]+[$]')";

	private const BATCH = 50;

	public function __construct(
		private readonly \PDO $pdo,
		private readonly string $usersTable,
	) {
	}

	/**
	 * The number of legacy hashes that are not wrapped yet.
	 */
	public function pending(): int {
		return (int) $this->pdo->query('SELECT COUNT(*) FROM ' . $this->usersTable . ' WHERE ' . self::PENDING)->fetchColumn();
	}

	/**
	 * Wraps legacy hashes until none are left or $seconds have passed (null:
	 * no limit). Returns [wrapped, still pending].
	 *
	 * @param (callable(int): void)|null $progress called with the number wrapped so far
	 * @return array{0: int, 1: int}
	 */
	public function wrap(?float $seconds = null, ?callable $progress = null): array {
		$deadline = $seconds === null ? null : microtime(true) + $seconds;
		$hasher = Password::hasher();
		$select = $this->pdo->prepare(
			'SELECT uid, pass, salt, enc_type FROM ' . $this->usersTable . ' WHERE uid > ? AND ' . self::PENDING . ' ORDER BY uid LIMIT ' . self::BATCH
		);
		$update = $this->pdo->prepare('UPDATE ' . $this->usersTable . ' SET pass = ? WHERE uid = ? AND pass = ?');

		$wrapped = 0;
		$cursor = 0;
		do {
			$select->execute([$cursor]);
			$rows = $select->fetchAll(\PDO::FETCH_ASSOC);
			foreach ($rows as $row) {
				$cursor = (int) $row['uid'];
				$value = Legacy::describe((string) $row['pass'], (string) $row['salt'], (int) $row['enc_type']);
				if (!Legacy::isUnwrapped($value)) {
					continue;
				}
				// unchanged since it was read: a concurrent login may have rehashed it
				$update->execute([Legacy::wrap($value, $hasher), $cursor, $row['pass']]);
				$wrapped += $update->rowCount();
				if ($progress !== null) {
					$progress($wrapped);
				}
				if ($deadline !== null && microtime(true) >= $deadline) {
					break 2;
				}
			}
		} while (count($rows) === self::BATCH);

		return [$wrapped, $this->pending()];
	}
}
