<?php

declare(strict_types=1);

namespace App\Preload;

use App\Legacy\LegacyRuntime;

/**
 * The site's preloads: where they are and which are disabled come from the
 * legacy constants (ICMS_PRELOAD_PATH, ICMS_TRUST_PATH), so they are resolved
 * after the constants stage, not at container compile time.
 */
final class Preloads
{
    public function __construct(private readonly LegacyRuntime $runtime)
    {
    }

    public function disabled(): DisabledPreloads
    {
        $this->runtime->bootTo('constants');

        return new DisabledPreloads(\Icms\Preload\Handler::disabledPreloadsFile());
    }

    public function inventory(): PreloadInventory
    {
        $this->runtime->bootTo('constants');

        return new PreloadInventory(\ICMS_PRELOAD_PATH, $this->disabled());
    }
}
