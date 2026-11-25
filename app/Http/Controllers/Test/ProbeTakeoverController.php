<?php

declare(strict_types=1);

namespace App\Http\Controllers\Test;

use Illuminate\Http\Response;

/**
 * Answers takeovers that tests register at runtime.
 */
final class ProbeTakeoverController
{
    public function __invoke(): Response
    {
        return new Response('host: probe', Response::HTTP_OK, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
