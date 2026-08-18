<?php

declare(strict_types=1);

namespace Icms\Admin\Config;

use icms;

/**
 * Configuration manager for the ImpressCMS admin control panel.
 *
 * Provides typed accessor/mutator methods for reading and writing site
 * configuration values, plus helpers for loading a configuration category and
 * rendering its edit form.
 *
 * @copyright The ImpressCMS Project <https://www.impresscms.org/>
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package   Icms\Admin\Config
 * @since     2.0
 */
class AdminConfigManager
{
    /**
     * In-memory cache keyed by `"$modId.$category"`.
     *
     * @var array<string, mixed[]>
     */
    private array $cache = [];

    /**
     * Retrieve a single configuration value.
     *
     * @param string $key      Configuration key name.
     * @param int    $modId    Module ID owning the config (1 = system).
     * @param int    $category Config category ID.
     *
     * @return mixed The stored value, or `null` when not found.
     */
    public function get(string $key, int $modId = 1, int $category = 0): mixed
    {
        $configs = $this->load($modId, $category);

        return $configs[$key] ?? null;
    }

    /**
     * Persist a single configuration value.
     *
     * @param string $key      Configuration key name.
     * @param mixed  $value    New value to store.
     * @param int    $modId    Module ID owning the config (1 = system).
     * @param int    $category Config category ID.
     *
     * @return bool `true` on success.
     */
    public function set(string $key, mixed $value, int $modId = 1, int $category = 0): bool
    {
        /** @var \icms_config_Handler $handler */
        $handler = icms::handler('icms_config');
        $configs = $handler->getConfigList($modId, $category);

        foreach ($configs as $config) {
            if ($config->getVar('conf_name') === $key) {
                $config->setVar('conf_value', $value);
                $result = $handler->insertConfig($config);

                // Invalidate cache entry.
                unset($this->cache[$modId . '.' . $category]);

                return $result;
            }
        }

        return false;
    }

    /**
     * Load and cache all configuration values for a module/category pair.
     *
     * Subsequent calls with the same `$modId`/`$category` are served from the
     * in-memory cache.
     *
     * @param int $modId    Module ID (1 = system).
     * @param int $category Config category ID.
     *
     * @return array<string, mixed> Associative map of key → value.
     */
    public function load(int $modId = 1, int $category = 0): array
    {
        $cacheKey = $modId . '.' . $category;

        if (!isset($this->cache[$cacheKey])) {
            /** @var \icms_config_Handler $handler */
            $handler = icms::handler('icms_config');
            $raw     = $handler->getConfigList($modId, $category);
            $result  = [];

            foreach ($raw as $config) {
                $result[$config->getVar('conf_name')] = $config->getVar('conf_value', 's');
            }

            $this->cache[$cacheKey] = $result;
        }

        return $this->cache[$cacheKey];
    }

    /**
     * Persist an associative array of configuration values for a module.
     *
     * @param array<string, mixed> $data     Key → value pairs to save.
     * @param int                  $modId    Module ID owning the config.
     * @param int                  $category Config category ID.
     *
     * @return bool `true` when every value was saved successfully.
     */
    public function save(array $data, int $modId = 1, int $category = 0): bool
    {
        $success = true;
        foreach ($data as $key => $value) {
            if (!$this->set((string) $key, $value, $modId, $category)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Render the HTML configuration form for a given module/category.
     *
     * Returns an HTML string built from {@see \icms_config_form_Base} so the
     * caller can echo it directly into a template.
     *
     * @param int    $modId    Module ID (1 = system).
     * @param int    $category Config category ID.
     * @param string $action   Form `action` URL.
     *
     * @return string Rendered HTML form.
     */
    public function renderForm(int $modId = 1, int $category = 0, string $action = ''): string
    {
        /** @var \icms_config_Handler $handler */
        $handler = icms::handler('icms_config');
        $configs = $handler->getConfigsByCat($category, $modId);

        $form = new \icms_config_form_Base(
            '',
            'icms_config_form',
            $action ?: $_SERVER['REQUEST_URI'],
            'post',
            true
        );

        foreach ($configs as $config) {
            $form->addElement($config->getFormElement());
        }

        ob_start();
        $form->display();

        return (string) ob_get_clean();
    }
}
