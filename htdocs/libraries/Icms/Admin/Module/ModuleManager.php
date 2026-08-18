<?php

declare(strict_types=1);

namespace Icms\Admin\Module;

use icms;
use icms_db_criteria_Compo;
use icms_db_criteria_Item;

/**
 * Module lifecycle management for the ImpressCMS admin control panel.
 *
 * Wraps the module handler operations (install, uninstall, update, activate,
 * deactivate, list) behind a clean object-oriented interface.
 *
 * @copyright The ImpressCMS Project <https://www.impresscms.org/>
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package   Icms\Admin\Module
 * @since     2.0
 */
class ModuleManager
{
    /**
     * Install a module identified by its directory name.
     *
     * Loads the module handler and delegates to the ImpressCMS module
     * installer.  Returns `true` on success, `false` on failure.
     *
     * @param string $dirname Module directory name (e.g. `'news'`).
     *
     * @return bool
     */
    public function install(string $dirname): bool
    {
        /** @var \icms_module_Handler $handler */
        $handler = icms::handler('icms_module');
        $module  = $handler->getByDirname($dirname);

        if ($module && $module->getVar('mid')) {
            // Module is already installed.
            return false;
        }

        return $handler->install($dirname);
    }

    /**
     * Uninstall a module identified by its directory name.
     *
     * @param string $dirname Module directory name.
     *
     * @return bool
     */
    public function uninstall(string $dirname): bool
    {
        /** @var \icms_module_Handler $handler */
        $handler = icms::handler('icms_module');
        $module  = $handler->getByDirname($dirname);

        if (!$module || !$module->getVar('mid')) {
            return false;
        }

        return $handler->uninstall($module);
    }

    /**
     * Update (re-install) a module identified by its directory name.
     *
     * @param string $dirname Module directory name.
     *
     * @return bool
     */
    public function update(string $dirname): bool
    {
        /** @var \icms_module_Handler $handler */
        $handler = icms::handler('icms_module');
        $module  = $handler->getByDirname($dirname);

        if (!$module || !$module->getVar('mid')) {
            return false;
        }

        return $handler->update($module);
    }

    /**
     * Activate a module so it appears in menus and can be accessed.
     *
     * @param string $dirname Module directory name.
     *
     * @return bool
     */
    public function activate(string $dirname): bool
    {
        /** @var \icms_module_Handler $handler */
        $handler = icms::handler('icms_module');
        $module  = $handler->getByDirname($dirname);

        if (!$module || !$module->getVar('mid')) {
            return false;
        }

        $module->setVar('isactive', 1);

        return $handler->insert($module);
    }

    /**
     * Deactivate a module so it is hidden from menus and cannot be accessed.
     *
     * @param string $dirname Module directory name.
     *
     * @return bool
     */
    public function deactivate(string $dirname): bool
    {
        /** @var \icms_module_Handler $handler */
        $handler = icms::handler('icms_module');
        $module  = $handler->getByDirname($dirname);

        if (!$module || !$module->getVar('mid')) {
            return false;
        }

        $module->setVar('isactive', 0);

        return $handler->insert($module);
    }

    /**
     * Return an array of all installed module objects.
     *
     * When `$activeOnly` is `true` only modules whose `isactive` flag is `1`
     * are included.
     *
     * @param bool $activeOnly Whether to restrict the list to active modules.
     *
     * @return \icms_module_Object[]
     */
    public function listModules(bool $activeOnly = false): array
    {
        /** @var \icms_module_Handler $handler */
        $handler = icms::handler('icms_module');

        if ($activeOnly) {
            $criteria = new icms_db_criteria_Compo();
            $criteria->add(new icms_db_criteria_Item('isactive', 1));

            return $handler->getObjects($criteria);
        }

        return $handler->getObjects();
    }
}
