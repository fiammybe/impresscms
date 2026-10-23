<?php
/**
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @category	ICMS
 * @package		Ipf
 * @subpackage	DiskFile
 */

declare(strict_types=1);

namespace Icms\Ipf\DiskFile;

use Icms\Db\Criteria\Element;

/**
 * Where a {@see TreeView} gets its nodes from. {@see FolderTree} reads them
 * from the disk; anything else that yields {@see TreeNode}s in tree order
 * (a folder tree of another store, a fixed list in a test) can stand in.
 */
interface TreeSource
{
	/**
	 * @return list<TreeNode> in tree order: a folder before what is in it
	 */
	public function nodes(string $folder, ?Element $criteria = null, string $sort = 'basename', string $order = 'ASC'): array;
}
