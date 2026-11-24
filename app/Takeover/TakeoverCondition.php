<?php

declare(strict_types=1);

namespace App\Takeover;

use Illuminate\Http\Request;

/**
 * A request condition of a takeover beyond its parameters, resolved from the
 * container for each request it could match.
 */
interface TakeoverCondition
{
    public function matches(Request $request): bool;
}
