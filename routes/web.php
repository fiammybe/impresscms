<?php

declare(strict_types=1);

use Illuminate\Http\Response;
use Illuminate\Routing\Router;

return static function (Router $router): void {
    $router->get('/up', static fn (): Response => new Response('ok', Response::HTTP_OK, [
        'Content-Type' => 'text/plain; charset=UTF-8',
    ]));
};
