<?php
/**
 * Icms\Ipf\Metadata\LegacyOperations
 *
 * The legacy admin URL of a handler and the `op` values its admin script
 * dispatches, so that another implementation can answer the same URLs.
 * Inert for legacy code: read by the Laravel host's definition compiler only.
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @category	ICMS
 * @package		Ipf
 */

namespace Icms\Ipf\Metadata;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class LegacyOperations {

	/**
	 * @param string $script the script path below the web root, for example /modules/system/admin.php
	 * @param array<string, string> $match request parameters that select the page, POST body over query string
	 * @param string $form the op showing the create or edit form
	 * @param string $save the op storing the posted form
	 * @param string $delete the op confirming and performing a deletion
	 * @param string $confirm the POST parameter set by the deletion confirmation
	 */
	public function __construct(
		public readonly string $script,
		public readonly array $match = [],
		public readonly string $form = 'mod',
		public readonly string $save = 'add',
		public readonly string $delete = 'del',
		public readonly string $confirm = 'confirm'
	) {
	}
}
