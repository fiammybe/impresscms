<?php

declare(strict_types=1);

namespace App\Ipf\Api;

use App\Legacy\LegacyRuntime;

/**
 * The objects modules expose through the API (design D6).
 *
 * A module opts in per object, in its manifest (icms_version.php):
 *
 *     $modversion['object_api']['post'] = [
 *         'read' => 'icms_perm:module_read', // security attribute, required
 *         'search' => ['title'],             // fields `q` looks in
 *         'fields' => ['title', 'summary'],  // fields returned; default: all stored
 *     ];
 *
 * Anything else is not exposed: an object without an entry, an entry without
 * a `read` attribute, an inactive or unknown module.
 */
final class ResourceRegistry
{
    /** @var array<string, array<string, mixed>> the `object_api` entries by module */
    private array $manifests = [];

    public function __construct(private readonly LegacyRuntime $runtime)
    {
    }

    public function find(string $module, string $item): ?ApiResource
    {
        if (preg_match('/^[a-z0-9_]+$/i', $module) !== 1 || preg_match('/^[a-z0-9_]+$/i', $item) !== 1) {
            return null;
        }

        $entry = $this->manifest($module)[$item] ?? null;
        if (!is_array($entry)) {
            return null;
        }

        $read = $entry['read'] ?? null;
        if (!is_string($read) || $read === '') {
            return null;
        }

        $fields = isset($entry['fields']) && is_array($entry['fields'])
            ? array_values(array_map('strval', $entry['fields']))
            : null;

        return new ApiResource(
            $module,
            $item,
            $read,
            array_values(array_map('strval', (array) ($entry['search'] ?? []))),
            $fields,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function manifest(string $dirname): array
    {
        if (isset($this->manifests[$dirname])) {
            return $this->manifests[$dirname];
        }

        $this->runtime->bootTo('module');
        $module = \icms::handler('icms_module')->getByDirname($dirname);
        if (!is_object($module) || !$module->getVar('isactive')) {
            return $this->manifests[$dirname] = [];
        }

        $entries = $module->getInfo('object_api');

        return $this->manifests[$dirname] = is_array($entries) ? $entries : [];
    }
}
