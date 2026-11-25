<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Module\ModuleRegistry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * `icms.module:<dirname>`: the route of a module's Laravel side answers 404
 * while the module is not installed and active, or its Laravel side is
 * switched off (App\Module\ModuleRegistry).
 */
final class RequiresActiveModule
{
    public function __construct(private readonly ModuleRegistry $modules)
    {
    }

    public function handle(Request $request, Closure $next, string $dirname): Response
    {
        abort_unless($this->modules->isEnabled($dirname), Response::HTTP_NOT_FOUND);

        return $next($request);
    }
}
