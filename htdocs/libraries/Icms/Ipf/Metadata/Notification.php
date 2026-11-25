<?php
/**
 * Icms\Ipf\Metadata\Notification
 *
 * A notification event the handler triggers (icms_data_notification), with
 * the handler event after which it is sent. Inert for legacy code: read by the
 * Laravel host's definition compiler only.
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @category	ICMS
 * @package		Ipf
 */

namespace Icms\Ipf\Metadata;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
final class Notification {

	/**
	 * @param string $category the notification category of the module
	 * @param string $event the notification event
	 * @param string $after the handler event: afterInsert, afterUpdate, afterSave or afterDelete
	 */
	public function __construct(
		public readonly string $category,
		public readonly string $event,
		public readonly string $after = 'afterInsert'
	) {
	}
}
