<?php

declare(strict_types=1);

namespace App\LegacyBridge;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class RequestOwnershipResolver
{
    /**
     * @param list<string> $reservedPaths
     */
    public function __construct(
        private readonly Router $router,
        private readonly array $reservedPaths = [],
    ) {
    }

    public function isLaravelOwned(Request $request): bool
    {
        $path = trim($request->path(), '/');

        foreach ($this->reservedPaths as $pattern) {
            if ($this->matchesPattern($path, $pattern)) {
                return true;
            }
        }

        try {
            $this->router->getRoutes()->match($request);

            return true;
        } catch (MethodNotAllowedHttpException) {
            return true;
        } catch (NotFoundHttpException) {
            return false;
        }
    }

    private function matchesPattern(string $path, string $pattern): bool
    {
        $pattern = trim($pattern, '/');

        if ($pattern === '') {
            return $path === '';
        }

        if (str_ends_with($pattern, '/*')) {
            $prefix = substr($pattern, 0, -2);

            return $path === $prefix || str_starts_with($path, $prefix . '/');
        }

        return $path === $pattern;
    }
}
