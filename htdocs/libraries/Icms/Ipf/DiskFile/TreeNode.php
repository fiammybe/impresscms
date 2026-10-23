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

/**
 * One row of a folder tree: a folder, or a file with its object.
 */
final class TreeNode
{
	/**
	 * @param string      $path       real path
	 * @param string|null $parentPath real path of the folder it is in; null for the top folder's own entries
	 * @param int         $level      0 for the entries of the top folder
	 */
	public function __construct(
		public readonly string $path,
		public readonly string $name,
		public readonly ?string $parentPath,
		public readonly int $level,
		public readonly ?Entity $file,
	) {
	}

	public function isFolder(): bool
	{
		return $this->file === null;
	}
}
