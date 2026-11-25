<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Admin\LegacyNavigation;
use App\Legacy\LegacyRuntime;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * POST requests need a valid ImpressCMS security token (icms::$security,
 * XOOPS_TOKEN_REQUEST), the CSRF protection of legacy forms; a token is used
 * up by its check.
 *
 * `icms.token` answers a missing or invalid token with 403. `icms.token:legacy`
 * answers as legacy admin pages do: back to the previous page with
 * _CORE_TOKENINVALID and the token errors.
 */
final class RequiresLegacyToken
{
    public function __construct(
        private readonly LegacyRuntime $runtime,
        private readonly LegacyNavigation $navigation,
    ) {
    }

    public function handle(Request $request, Closure $next, string $failure = 'forbidden'): Response
    {
        if (! $request->isMethod('POST')) {
            return $next($request);
        }

        // the token names and messages are defined by the core language files
        $this->runtime->bootTo('language');
        $security = $this->runtime->service('security');
        if ($security->check()) {
            return $next($request);
        }

        if ($failure === 'legacy') {
            $errors = implode('<br />', (array) $security->getErrors());

            return $this->navigation->back(3, _CORE_TOKENINVALID . "<br />{$errors}");
        }

        abort(Response::HTTP_FORBIDDEN, 'Invalid or expired ImpressCMS security token.');
    }
}
