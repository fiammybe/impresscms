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

/**
 * A video file. PHP cannot read a video's duration without an extra library,
 * so this type adds nothing to {@see Entity} yet; it lets callers tell videos
 * apart by class.
 */
class VideoEntity extends Entity
{
}
