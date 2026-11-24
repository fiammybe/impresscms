<?php

declare(strict_types=1);

namespace App\Http\Controllers\Test;

use App\Takeover\TakeoverCondition;
use Illuminate\Http\Request;

/**
 * Bridge tests that need a claimed legacy URL themselves send
 * `X-Icms-Test-Takeover: off`.
 */
final class TakeoverNotSuppressed implements TakeoverCondition
{
    public function matches(Request $request): bool
    {
        return $request->headers->get('X-Icms-Test-Takeover') !== 'off';
    }
}
