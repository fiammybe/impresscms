<?php
/**
 * Icms\Ipf\Metadata\ListColumn
 *
 * A column of the handler's admin list, as icms_ipf_view_Column declares it in
 * an admin script. Inert for legacy code: read by the Laravel host's
 * definition compiler only.
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @category	ICMS
 * @package		Ipf
 */

namespace Icms\Ipf\Metadata;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
final class ListColumn {

	/**
	 * @param string $field the variable shown in the column
	 * @param string $align left, center or right (_GLOBAL_LEFT, …)
	 * @param int|null $width in pixels; null for the remaining width
	 * @param bool $sortable whether the header links to a sorted list
	 */
	public function __construct(
		public readonly string $field,
		public readonly string $align = 'left',
		public readonly ?int $width = null,
		public readonly bool $sortable = true
	) {
	}
}
