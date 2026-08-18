<?php

declare(strict_types=1);

namespace Icms\Admin\Module;

use icms;

/**
 * Module metadata reader for the ImpressCMS admin control panel.
 *
 * Provides typed accessors for the static information declared in a module's
 * `icms_version.php` file (version, credits, help URL, …).
 *
 * @copyright The ImpressCMS Project <https://www.impresscms.org/>
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package   Icms\Admin\Module
 * @since     2.0
 */
class ModuleInfo
{
    /**
     * Return the full `getInfo()` array for the module identified by `$dirname`.
     *
     * The returned array is the one defined in the module's `icms_version.php`
     * (keys: `name`, `version`, `description`, `author`, `credits`, `help`, …).
     * Returns `null` when the module is not found.
     *
     * @param string $dirname Module directory name (e.g. `'news'`).
     *
     * @return array<string, mixed>|null Module info array, or `null`.
     */
    public function getInfo(string $dirname): ?array
    {
        /** @var \icms_module_Handler $handler */
        $handler = icms::handler('icms_module');
        $module  = $handler->getByDirname($dirname);

        if (!$module) {
            return null;
        }

        return $module->getInfo() ?: null;
    }

    /**
     * Return the version string declared in a module's `icms_version.php`.
     *
     * Returns `null` when the module is not found.
     *
     * @param string $dirname Module directory name.
     *
     * @return string|null Version string (e.g. `'1.3'`), or `null`.
     */
    public function getVersion(string $dirname): ?string
    {
        $info = $this->getInfo($dirname);

        if ($info === null || !isset($info['version'])) {
            return null;
        }

        return (string) $info['version'];
    }

    /**
     * Return the credits text/URL declared in a module's `icms_version.php`.
     *
     * Returns `null` when the module is not found or no credits key is set.
     *
     * @param string $dirname Module directory name.
     *
     * @return string|null Credits text, or `null`.
     */
    public function getCredits(string $dirname): ?string
    {
        $info = $this->getInfo($dirname);

        if ($info === null || !isset($info['credits'])) {
            return null;
        }

        return (string) $info['credits'];
    }

    /**
     * Return the help URL declared in a module's `icms_version.php`.
     *
     * Returns `null` when the module is not found or no help key is set.
     *
     * @param string $dirname Module directory name.
     *
     * @return string|null Help URL, or `null`.
     */
    public function getHelp(string $dirname): ?string
    {
        $info = $this->getInfo($dirname);

        if ($info === null || !isset($info['help'])) {
            return null;
        }

        return (string) $info['help'];
    }
}
