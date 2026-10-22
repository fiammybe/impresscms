<?php

/**
 * Smarty plugin: the output of a capability for an IPF object
 *
 * <{icms_capability name="tagging" object=$article variant="list"}>
 * <{icms_capability name="tagging" module="news" item="article" id=$article.article_id variant="cloud"}>
 *
 * Renders an empty string when the capability is not enabled for the
 * object's type or has no active provider (spec capability-ipf-integration).
 * The other parameters are passed to the provider.
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		ICMS\Capability
 */

declare(strict_types=1);

use Icms\Capability\Integration;
use Icms\Capability\ObjectRef;

/**
 * @param array<string, mixed> $params
 */
function smarty_function_icms_capability(array $params, &$smarty): string
{
    $name = (string) ($params['name'] ?? '');
    $variant = (string) ($params['variant'] ?? 'list');

    try {
        $ref = isset($params['object']) && is_object($params['object'])
            ? ObjectRef::fromEntity($params['object'])
            : new ObjectRef((string) ($params['module'] ?? ''), (string) ($params['item'] ?? ''), (int) ($params['id'] ?? 0));
    } catch (\InvalidArgumentException) {
        return '';
    }

    unset($params['name'], $params['variant'], $params['object'], $params['module'], $params['item'], $params['id']);

    return Integration::instance()->render($name, $ref, $variant, $params);
}
