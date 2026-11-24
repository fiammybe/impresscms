<?php

declare(strict_types=1);

namespace App\Module;

use Illuminate\Database\DatabaseManager;

/**
 * Whether an ImpressCMS module's Laravel side serves requests: the module is
 * installed and active in ImpressCMS (its `modules` row), and not switched
 * off with ICMS_MODULES_LARAVEL_DISABLED.
 *
 * Read per request, when a request could be served by the module, so that
 * installing, deactivating or uninstalling a module in the legacy admin takes
 * effect without rebuilding the route cache.
 */
final class ModuleRegistry
{
    /** @var array<string, bool> */
    private array $active = [];

    public function __construct(private readonly DatabaseManager $db)
    {
    }

    public function isEnabled(string $dirname): bool
    {
        if (in_array($dirname, (array) config('icms.modules.laravel_disabled', []), true)) {
            return false;
        }

        return $this->active[$dirname] ??= (int) $this->db->connection('icms')
            ->table('modules')
            ->where('dirname', $dirname)
            ->value('isactive') === 1;
    }

    /**
     * Forgets the states read in this request.
     */
    public function refresh(): void
    {
        $this->active = [];
    }
}
