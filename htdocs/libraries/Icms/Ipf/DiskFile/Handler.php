<?php
/**
 * Lists and loads the files of a folder as IPF-style objects.
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @category	ICMS
 * @package		Ipf
 * @subpackage	DiskFile
 */

declare(strict_types=1);

namespace Icms\Ipf\DiskFile;

use Icms\Db\Criteria\Compo;
use Icms\Db\Criteria\Element;
use Icms\Db\Criteria\Item;

defined("ICMS_ROOT_PATH") or die("ImpressCMS root path not defined");

/**
 * Every path the handler reads or acts on must lie inside its root; symbolic
 * links are resolved first, so a link cannot lead out of it.
 *
 * The files of one folder are filtered in memory with the existing criteria
 * classes, on the fields `basename`, `extension`, `size` and `mtime`, with the
 * operators `=`, `!=`, `<>`, `<`, `<=`, `>`, `>=`, `LIKE`, `NOT LIKE`, `IN` and
 * `NOT IN`. Anything else is refused with an {@see \InvalidArgumentException}
 * rather than ignored, so a filter never silently matches everything.
 */
class Handler
{
	public const CRITERIA_FIELDS = ['basename', 'extension', 'size', 'mtime'];
	public const SORT_FIELDS = ['basename', 'extension', 'size', 'mtime', 'path'];

	private const NUMERIC_FIELDS = ['size', 'mtime'];

	private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
	private const VIDEO_EXTENSIONS = ['mp4', 'mov', 'avi', 'mkv', 'webm', 'ogv', 'wmv'];

	public string $keyName = 'path';
	public string $identifierName = 'basename';

	private readonly string $root;

	/**
	 * @param string $root the folder whose files (and those of its subfolders) the handler may touch
	 *
	 * @throws Exception when $root is not an existing folder
	 */
	public function __construct(string $root)
	{
		$real = $root === '' ? false : realpath($root);
		if ($real === false || !is_dir($real)) {
			throw new Exception(sprintf('Disk folder not found: "%s".', $root));
		}

		$this->root = $real;
	}

	public function root(): string
	{
		return $this->root;
	}

	/**
	 * @throws Exception when $path is not a file inside the root
	 */
	public function get(string $path): Entity
	{
		$resolved = $this->resolveFile($path);

		$class = self::classFor($resolved);
		$entity = new $class($this);
		$entity->loadFromPath($resolved);

		return $entity;
	}

	/**
	 * @return array<string, Entity> the files, keyed by path
	 *
	 * @throws Exception                 when $folder is not a folder inside the root
	 * @throws \InvalidArgumentException for criteria this handler cannot evaluate
	 */
	public function getObjects(string $folder, ?Element $criteria = null, string $sort = 'basename', string $order = 'ASC'): array
	{
		$folder = $this->resolveFolder($folder);

		$objects = [];
		foreach (new \FilesystemIterator($folder, \FilesystemIterator::SKIP_DOTS) as $info) {
			// symbolic links are resolved, so a link to a file outside the root is skipped
			if (!$info->isFile() || !$this->isInside((string) $info->getRealPath())) {
				continue;
			}

			$entity = $this->get($info->getPathname());
			if ($criteria === null || $this->matches($entity, $criteria)) {
				$objects[] = $entity;
			}
		}

		$this->sort($objects, $sort, $order);

		if ($criteria !== null && ($criteria->getStart() > 0 || $criteria->getLimit() > 0)) {
			$objects = array_slice($objects, $criteria->getStart(), $criteria->getLimit() > 0 ? $criteria->getLimit() : null);
		}

		$keyed = [];
		foreach ($objects as $entity) {
			$keyed[$entity->path()] = $entity;
		}

		return $keyed;
	}

	/**
	 * @return array<string, string> the base names of the files, keyed by path
	 *
	 * @throws Exception
	 * @throws \InvalidArgumentException
	 */
	public function getList(string $folder, ?Element $criteria = null, string $sort = 'basename', string $order = 'ASC'): array
	{
		return array_map(
			static fn (Entity $entity): string => (string) $entity->getVar('basename', 'n'),
			$this->getObjects($folder, $criteria, $sort, $order),
		);
	}

	/**
	 * The folders directly inside $folder, natural case-insensitive order.
	 * Symbolic links are not followed, so a link cannot create a loop or lead
	 * out of the root.
	 *
	 * @return array<string, string> the base names of the folders, keyed by path
	 *
	 * @throws Exception when $folder is not a folder inside the root
	 */
	public function getFolders(string $folder): array
	{
		$folder = $this->resolveFolder($folder);

		$folders = [];
		foreach (new \FilesystemIterator($folder, \FilesystemIterator::SKIP_DOTS) as $info) {
			if ($info->isLink() || !$info->isDir() || !$this->isInside((string) $info->getRealPath())) {
				continue;
			}

			$folders[(string) $info->getRealPath()] = $info->getFilename();
		}

		uasort($folders, strnatcasecmp(...));

		return $folders;
	}

	/**
	 * @return string the real path of an existing file inside the root
	 *
	 * @throws Exception
	 */
	public function resolveFile(string $path): string
	{
		$real = $path === '' ? false : realpath($path);
		if ($real === false || !is_file($real) || !$this->isInside($real)) {
			throw new Exception(sprintf('Disk file not found: "%s".', $path));
		}

		return $real;
	}

	/**
	 * @return string the real path of an existing folder inside the root
	 *
	 * @throws Exception
	 */
	public function resolveFolder(string $path): string
	{
		$real = $path === '' ? false : realpath($path);
		if ($real === false || !is_dir($real) || !$this->isInside($real)) {
			throw new Exception(sprintf('Disk folder not found: "%s".', $path));
		}

		return $real;
	}

	/**
	 * @return class-string<Entity>
	 */
	private static function classFor(string $path): string
	{
		$extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

		return match (true) {
			in_array($extension, self::IMAGE_EXTENSIONS, true) => ImageEntity::class,
			in_array($extension, self::VIDEO_EXTENSIONS, true) => VideoEntity::class,
			$extension === 'zip' => ZipEntity::class,
			default => Entity::class,
		};
	}

	private function isInside(string $realPath): bool
	{
		return $realPath === $this->root || str_starts_with($realPath, rtrim($this->root, '/\\') . DIRECTORY_SEPARATOR);
	}

	private function matches(Entity $entity, Element $criteria): bool
	{
		if ($criteria instanceof Compo) {
			return $this->matchesCompo($entity, $criteria);
		}

		if ($criteria instanceof Item) {
			return $this->matchesItem($entity, $criteria);
		}

		throw new \InvalidArgumentException(sprintf('Unsupported criteria class %s.', $criteria::class));
	}

	private function matchesCompo(Entity $entity, Compo $criteria): bool
	{
		$result = true;

		foreach ($criteria->criteriaElements as $index => $element) {
			$matches = $this->matches($entity, $element);

			if ($index === 0) {
				$result = $matches;
			} elseif (strtoupper($criteria->conditions[$index] ?? 'AND') === 'OR') {
				$result = $result || $matches;
			} else {
				$result = $result && $matches;
			}
		}

		return $result;
	}

	private function matchesItem(Entity $entity, Item $criteria): bool
	{
		$column = $criteria->getColumn();
		if (!in_array($column, self::CRITERIA_FIELDS, true)) {
			throw new \InvalidArgumentException(sprintf('Unsupported disk file criteria field "%s".', $column));
		}

		$numeric = in_array($column, self::NUMERIC_FIELDS, true);
		$actual = $entity->getVar($column, 'n');
		$actual = $numeric ? (int) $actual : (string) $actual;
		$value = $criteria->getValue();
		$operator = strtoupper(trim($criteria->getOperator()));

		// extensions are stored lower-cased, so they are compared that way
		$normalise = static fn (string $text): string => $column === 'extension' ? strtolower($text) : $text;
		$compare = static fn (string $text): int|string => $numeric ? (int) $text : $normalise($text);

		return match ($operator) {
			'=' => $actual === $compare($value),
			'!=', '<>' => $actual !== $compare($value),
			'<' => $actual < $compare($value),
			'<=' => $actual <= $compare($value),
			'>' => $actual > $compare($value),
			'>=' => $actual >= $compare($value),
			'LIKE' => $this->like((string) $actual, $normalise($value)),
			'NOT LIKE' => !$this->like((string) $actual, $normalise($value)),
			'IN' => in_array($actual, array_map($compare, self::setValues($value)), true),
			'NOT IN' => !in_array($actual, array_map($compare, self::setValues($value)), true),
			default => throw new \InvalidArgumentException(sprintf('Unsupported disk file criteria operator "%s".', $operator)),
		};
	}

	/**
	 * SQL LIKE: % is any run of characters, _ is one character. Case-insensitive,
	 * as it is in MySQL's default collation.
	 */
	private function like(string $actual, string $pattern): bool
	{
		$regex = '/^' . strtr(preg_quote($pattern, '/'), ['%' => '.*', '_' => '.']) . '$/isu';

		return preg_match($regex, $actual) === 1;
	}

	/**
	 * The values of an IN list as the criteria store them: "(a,b,'c')".
	 *
	 * @return list<string>
	 */
	private static function setValues(string $value): array
	{
		$value = trim(trim($value), '()');
		if ($value === '') {
			return [];
		}

		return array_map(
			static fn (string $item): string => trim(trim($item), '\'"'),
			explode(',', $value),
		);
	}

	/**
	 * @param list<Entity> $objects
	 */
	private function sort(array &$objects, string $sort, string $order): void
	{
		if (!in_array($sort, self::SORT_FIELDS, true)) {
			$sort = 'basename';
		}

		$direction = strtoupper($order) === 'DESC' ? -1 : 1;
		$numeric = in_array($sort, self::NUMERIC_FIELDS, true);

		usort($objects, static function (Entity $a, Entity $b) use ($sort, $direction, $numeric): int {
			$left = $a->getVar($sort, 'n');
			$right = $b->getVar($sort, 'n');

			return $direction * ($numeric ? (int) $left <=> (int) $right : strnatcasecmp((string) $left, (string) $right));
		});
	}
}
