<?php

declare(strict_types=1);

/*
 * The IPF resources of the system module compiled into Laravel artifacts
 * (`php artisan icms:definitions:compile --module=system`), and the
 * controllers serving them.
 *
 * - handler: the IPF handler, as App\Legacy\HandlerLocator names it
 * - controller: the resource controller for the legacy admin URL the handler
 *   declares (Icms\Ipf\Metadata\LegacyOperations)
 * - middleware: run before the controller, on the legacy URL and the routes
 */
return [
    'mimetype' => [
        'handler' => 'module:system/mimetype',
        'controller' => Modules\System\Http\MimetypeController::class,
        'middleware' => ['icms.admin:mimetype'],
    ],
];
