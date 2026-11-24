<?php
/**
 * Icms\Ipf\Metadata\ListFilter
 *
 * A filter of the handler's admin list (icms_ipf_view_Table::addFilter()):
 * a variable and the handler method that lists its choices. Inert for legacy
 * code: read by the Laravel host's definition compiler only.
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @category	ICMS
 * @package		Ipf
 */

namespace Icms\Ipf\Metadata;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
final class ListFilter {

	public function __construct(
		public readonly string $field,
		public readonly string $method,
		public readonly ?string $default = null
	) {
	}
}
