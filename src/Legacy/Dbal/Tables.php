<?php

declare(strict_types=1);

namespace App\Legacy\Dbal;

use App\Legacy\LegacyRuntime;

/**
 * Names of ImpressCMS tables for DBAL queries: the prefix of mainfile.php
 * (XOOPS_DB_PREFIX) and the table name, as icms::$xoopsDB->prefix() builds it.
 * Reads mainfile.php only (the `constants` stage), so it boots no services.
 */
final class Tables
{
    public function __construct(private readonly LegacyRuntime $runtime)
    {
    }

    public function name(string $table): string
    {
        $this->runtime->bootTo('constants');
        $prefix = defined('XOOPS_DB_PREFIX') ? (string) constant('XOOPS_DB_PREFIX') : '';

        return $prefix === '' ? $table : $prefix . '_' . $table;
    }
}
