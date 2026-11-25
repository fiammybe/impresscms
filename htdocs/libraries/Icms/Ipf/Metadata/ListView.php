<?php
/**
 * Icms\Ipf\Metadata\ListView
 *
 * The handler's admin list as an icms_ipf_view_Table builds it in an admin
 * script: row actions, the create button, the quick search and the default
 * order. Inert for legacy code: read by the Laravel host's definition
 * compiler only.
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @category	ICMS
 * @package		Ipf
 */

namespace Icms\Ipf\Metadata;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class ListView {

	/**
	 * @param list<string> $actions row actions of icms_ipf_view_Table (edit, delete)
	 * @param list<string> $quickSearch variables searched with LIKE, combined with OR
	 * @param bool $createButton whether the list has the intro button to the create form
	 * @param string|null $sortBy default sort variable; null for the handler's identifier
	 * @param string $order default order, ASC or DESC
	 * @param int $limit default page size
	 */
	public function __construct(
		public readonly array $actions = ['edit', 'delete'],
		public readonly array $quickSearch = [],
		public readonly bool $createButton = false,
		public readonly ?string $sortBy = null,
		public readonly string $order = 'ASC',
		public readonly int $limit = 15
	) {
	}
}
