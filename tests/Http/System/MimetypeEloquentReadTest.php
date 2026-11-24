<?php

declare(strict_types=1);

namespace App\Tests\Http\System;

use App\Tests\Support\ServerProcess;
use PHPUnit\Framework\Attributes\Group;

/**
 * MimetypeBladeTest with reads through the generated Eloquent model
 * (ICMS_SYSTEM_MIMETYPE_PERSISTENCE=eloquent-read): lists, search, sort and
 * edit forms read the legacy table with Eloquent and the permissions from
 * group_permission; saving and deleting still go through the IPF handler.
 * The same assertions hold, so both read the rows legacy writes.
 */
#[Group('spec:system-mimetype-admin')]
final class MimetypeEloquentReadTest extends MimetypeBladeTest
{
    protected static function createServer(): ServerProcess
    {
        return ServerProcess::forSite([
            'ICMS_SYSTEM_MIMETYPE_RENDERER' => 'blade',
            'ICMS_SYSTEM_MIMETYPE_PERSISTENCE' => 'eloquent-read',
        ]);
    }
}
