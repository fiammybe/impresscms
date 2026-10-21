<?php

namespace Icms\View\Smarty;

use Smarty\Smarty;

/**
 * Registers everything ImpressCMS adds to a Smarty engine: the ImpressCMS
 * tags, the legacy syntax pre-filter and, optionally, the `db:` resource.
 */
class Setup
{
    public static function apply(Smarty $smarty, bool $withDatabaseResource = true): void
    {
        // Smarty 2 accepted "<{ if ...}>"; Smarty 5 treats a delimiter followed by a space as literal text
        $smarty->setAutoLiteral(false);
        $smarty->registerFilter('pre', new LegacySyntaxFilter(), 'icms_legacy_syntax');

        foreach (Functions::tags() as $tag => $method) {
            $smarty->registerPlugin('function', $tag, [Functions::class, $method]);
        }

        if ($withDatabaseResource) {
            $smarty->registerResource('db', new DbResource());
        }
    }
}
