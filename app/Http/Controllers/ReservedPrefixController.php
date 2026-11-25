<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\Response;

/**
 * A path under a reserved prefix that no Laravel route claims: 404, never
 * the legacy front controller.
 */
final class ReservedPrefixController
{
    public function __invoke(): never
    {
        abort(Response::HTTP_NOT_FOUND);
    }
}
