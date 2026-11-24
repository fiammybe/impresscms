<?php

declare(strict_types=1);

namespace Modules\System;

use App\Module\ModuleServiceProvider;

/**
 * The Laravel side of the system module (htdocs/modules/system): its migrated
 * IPF resources (resources.php), their switches (config.php), views and
 * routes. Its other pages stay legacy.
 */
final class SystemModuleServiceProvider extends ModuleServiceProvider
{
    public function dirname(): string
    {
        return 'system';
    }
}
