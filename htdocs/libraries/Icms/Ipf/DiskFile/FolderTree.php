<?php
/**
 * The folders and files below a folder, as a flat list in tree order.
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @category	ICMS
 * @package		Ipf
 * @subpackage	DiskFile
 */

declare(strict_types=1);

namespace Icms\Ipf\DiskFile;

use Icms\Db\Criteria\Element;

defined("ICMS_ROOT_PATH") or die("ImpressCMS root path not defined");

/**
 * Walks a folder depth first: in every folder its sub folders come first, then
 * its files. The files come from {@see Handler::getObjects()}, so criteria and
 * sorting work as there and every path stays inside the handler's root. Sub
 * folders are listed with {@see Handler::getFolders()}, which does not follow
 * symbolic links, so the walk always ends; $maxDepth bounds it further.
 *
 * With criteria, a folder that holds no matching file at any depth is left out.
 * The criteria's start and limit do not apply: a tree shows everything it has.
 */
final class FolderTree
{
	public function __construct(
		private readonly Handler $handler,
		private readonly int $maxDepth = 8,
	) {
		if ($maxDepth < 1) {
			throw new \InvalidArgumentException('The maximum depth is at least 1.');
		}
	}

	/**
	 * @return list<TreeNode>
	 *
	 * @throws Exception                 when $folder is not a folder inside the root
	 * @throws \InvalidArgumentException for criteria the handler cannot evaluate
	 */
	public function nodes(string $folder, ?Element $criteria = null, string $sort = 'basename', string $order = 'ASC'): array
	{
		return $this->walk($this->handler->resolveFolder($folder), null, 0, $criteria, $sort, $order);
	}

	/**
	 * @return list<TreeNode>
	 */
	private function walk(string $folder, ?string $parent, int $level, ?Element $criteria, string $sort, string $order): array
	{
		$nodes = [];

		if ($level < $this->maxDepth) {
			foreach ($this->handler->getFolders($folder) as $path => $name) {
				$below = $this->walk($path, $path, $level + 1, $criteria, $sort, $order);

				if ($criteria !== null && $below === []) {
					continue;
				}

				$nodes[] = new TreeNode($path, $name, $parent, $level, null);
				array_push($nodes, ...$below);
			}
		}

		foreach ($this->handler->getObjects($folder, $criteria, $sort, $order) as $path => $file) {
			$nodes[] = new TreeNode($path, (string) $file->getVar('basename', 'n'), $parent, $level, $file);
		}

		return $nodes;
	}
}
