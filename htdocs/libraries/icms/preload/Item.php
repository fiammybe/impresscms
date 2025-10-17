<?php
/**
 * icms_preload_Item
 *
 * Class which is extended by any preload item.
 *
 * @deprecated Legacy preloads remain supported for backward compatibility.
 *             Prefer registering PSR-14 listeners with league/event and using typed events.
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @category	ICMS
 * @package		Preload
 * @since		1.1
 * @author		marcan <marcan@impresscms.org>
 * @version		SVN: $Id: Item.php 10326 2010-07-11 18:54:25Z malanciault $
 */

class icms_preload_Item {

	public function __construct() {
		// Deprecated: legacy preload item base class
		static $emitted = false;
		if (!$emitted && class_exists('icms_core_Debug')) {
			$emitted = true;
			icms_core_Debug::setDeprecated(
				'icms_preload_Item',
				'Migrate away from extending icms_preload_Item. Register PSR-14 listeners via icms::$events->subscribeTo(). For legacy string events, subscribe to \\ImpressCMS\\Events\\LegacyEvent and filter by $event->name.'
			);
		}
	}
}
