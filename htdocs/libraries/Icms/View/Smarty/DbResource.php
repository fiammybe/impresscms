<?php

namespace Icms\View\Smarty;

use Smarty\Resource\CustomPlugin;

/**
 * Smarty resource `db:` — loads templates from the template set in the
 * database, with an override from the theme or the module's own folder.
 */
class DbResource extends CustomPlugin
{
    /** @var array<string, \Icms\View\Template\File\Entity|string|false> */
    private static array $cache = [];

    protected function fetch($name, &$source, &$mtime): void
    {
        $template = self::templateInfo((string) $name);

        if (! $template) {
            return;
        }

        if (is_object($template)) {
            $source = $template->getVar('tpl_source', 'n');
            $mtime = $template->getVar('tpl_lastmodified', 'n');

            return;
        }

        $source = file_get_contents($template);
        $mtime = filemtime($template);
    }

    /**
     * @return \Icms\View\Template\File\Entity|string|false the database entity, a file path or false
     */
    public static function templateInfo(string $name)
    {
        global $icmsConfig;

        if (isset(self::$cache[$name])) {
            return self::$cache[$name];
        }

        $templateSet = $icmsConfig['template_set'];
        $theme = $icmsConfig['theme_set'] ?? 'default';
        $handler = \icms::handler('icms_view_template_file');

        if ($templateSet != 'default') {
            $found = $handler->getPrefetchedBlock($templateSet, $name);

            if (count($found)) {
                return self::$cache[$name] = $found[0];
            }
        }

        $found = $handler->getPrefetchedBlock('default', $name);

        if (! count($found)) {
            return self::$cache[$name] = false;
        }

        $entity = $found[0];
        $module = $entity->getVar('tpl_module', 'n');
        $blockPath = $entity->getVar('tpl_type', 'n') == 'block' ? 'blocks/' : '';

        $themeBase = is_dir(ICMS_MODULES_PATH . '/system/themes/' . $theme) ?
            ICMS_MODULES_PATH . '/system/themes/' . $theme :
            ICMS_THEME_PATH . "/$theme";

        $path = $themeBase . "/modules/$module/$blockPath$name";

        if (! file_exists($path)) {
            $path = ICMS_ROOT_PATH . "/modules/$module/templates/$blockPath$name";
        }

        return self::$cache[$name] = file_exists($path) ? $path : $entity;
    }
}
