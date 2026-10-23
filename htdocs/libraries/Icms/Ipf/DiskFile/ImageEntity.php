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

class ImageEntity extends Entity
{
	/**
	 * @return array{width: int, height: int}|null null when the file is not an image PHP can read
	 */
	public function getDimensions(): ?array
	{
		$dimensions = @getimagesize($this->path());

		return is_array($dimensions)
			? ['width' => (int) $dimensions[0], 'height' => (int) $dimensions[1]]
			: null;
	}
}
