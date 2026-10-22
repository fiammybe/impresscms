<?php

declare(strict_types=1);

namespace App\Capability;

use App\Legacy\LegacyRuntime;
use Icms\Capability\Contract\BaseInterface;
use Icms\Capability\Registry;

/**
 * The capability registry legacy code uses, for the container (design D14):
 * legacy is booted to the `module` stage first, because the registry reads the
 * system module's tables and the providers' module handlers.
 */
final class RegistryFactory
{
    public function __construct(private readonly LegacyRuntime $runtime)
    {
    }

    public function registry(): Registry
    {
        $this->runtime->bootTo('module');

        return Registry::instance();
    }

    /**
     * The active provider's implementation of a capability's contract.
     *
     * @throws \Icms\Capability\Exception\UnavailableException when the capability has no active provider
     */
    public function capability(string $name): BaseInterface
    {
        $this->registry();

        return Registry::capability($name);
    }
}
