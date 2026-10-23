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

defined("ICMS_ROOT_PATH") or die("ImpressCMS root path not defined");

class ZipEntity extends Entity
{
	/**
	 * @return int|null null when the archive cannot be opened or zip support is missing
	 */
	public function getEntryCount(): ?int
	{
		if (!class_exists(\ZipArchive::class)) {
			return null;
		}

		$zip = new \ZipArchive();
		if ($zip->open($this->path(), \ZipArchive::RDONLY) !== true) {
			return null;
		}

		$count = $zip->numFiles;
		$zip->close();

		return $count;
	}
}
