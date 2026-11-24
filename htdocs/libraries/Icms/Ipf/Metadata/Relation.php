<?php
/**
 * Icms\Ipf\Metadata\Relation
 *
 * An explicit relation of a variable to another IPF resource. A control with
 * an itemHandler only offers choices; only this attribute makes a variable a
 * relation. Inert for legacy code: read by the Laravel host's definition
 * compiler only.
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @category	ICMS
 * @package		Ipf
 */

namespace Icms\Ipf\Metadata;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
final class Relation {

	public const BELONGS_TO = 'belongsTo';
	public const HAS_MANY = 'hasMany';
	public const BELONGS_TO_MANY = 'belongsToMany';

	/**
	 * @param string $field the local variable holding the reference
	 * @param string $target the related resource, `<module>.<item>`
	 * @param string $cardinality belongsTo, hasMany or belongsToMany
	 * @param string $ownership `owner` when deleting this item must delete the related ones, otherwise `reference`
	 * @param string|null $foreignKey the referenced column; null for the target's key
	 * @param string|null $joinTable the unprefixed join table of a belongsToMany relation
	 */
	public function __construct(
		public readonly string $field,
		public readonly string $target,
		public readonly string $cardinality = self::BELONGS_TO,
		public readonly string $ownership = 'reference',
		public readonly ?string $foreignKey = null,
		public readonly ?string $joinTable = null
	) {
	}
}
