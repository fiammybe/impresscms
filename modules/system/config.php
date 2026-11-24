<?php

declare(strict_types=1);

/*
 * Switches of the system module's Laravel side, merged as
 * config('icms_modules.system'). Each one rolls back one concern of a
 * resource to its legacy implementation; none needs a code change. The
 * whole legacy URL goes back to the legacy script with
 * ICMS_TAKEOVERS_DISABLED=system.mimetype (or every resource of the module
 * with ICMS_MODULES_LARAVEL_DISABLED=system).
 */
return [
    'resources' => [
        'mimetype' => [
            // blade | legacy: the IPF form and table of the legacy page
            'renderer' => env('ICMS_SYSTEM_MIMETYPE_RENDERER', 'blade'),
            // legacy | eloquent-read: lists and reads with the generated Eloquent model; writes stay on the IPF handler
            'persistence' => env('ICMS_SYSTEM_MIMETYPE_PERSISTENCE', 'legacy'),
            // form-request | legacy: only the handler's own checks (cleanVars)
            'validation' => env('ICMS_SYSTEM_MIMETYPE_VALIDATION', 'form-request'),
            // generated | legacy: only the icms.admin middleware decides access
            'policy' => env('ICMS_SYSTEM_MIMETYPE_POLICY', 'generated'),
        ],
    ],
];
