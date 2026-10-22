<?php

/**
 * Which capabilities are enabled for which IPF object types
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		ICMS\Capability
 */

declare(strict_types=1);

namespace Icms\Capability;

use Icms\Capability\Config\AssignmentHandler;
use Throwable;

/**
 * The assignment matrix of the capabilities page, combined with the consumer
 * declarations and the provider states (design D9). The matrix is read once
 * per request; object types without an enabled cell cost no further work.
 */
final class AssignmentMap
{
    /** @var array<string, list<string>> enabled capabilities by object type */
    private array $enabled = [];

    public function __construct(
        private readonly Registry $registry,
        private readonly AssignmentHandler $handler,
    ) {
    }

    /**
     * True only when the cell is enabled, the consumer does not declare the
     * capability unsupported, the capability is active, and its active
     * provider serves the object type.
     */
    public function isEnabled(string $objectType, string $capability): bool
    {
        return in_array($capability, $this->enabledFor($objectType), true);
    }

    /** @return list<string> the capabilities enabled for the object type */
    public function enabledFor(string $objectType): array
    {
        if (isset($this->enabled[$objectType])) {
            return $this->enabled[$objectType];
        }

        $enabled = [];

        foreach ($this->cells($objectType) as $capability => $cell) {
            if (! $cell->isEnabled()) {
                continue;
            }

            if (! Definitions::has($capability)) {
                continue;
            }

            if ($this->registry->snapshot()->isUnsupported($objectType, $capability)) {
                continue;
            }

            if ($this->registry->stateOf($capability) !== State::Active) {
                continue;
            }

            if (! $this->serves($capability, $objectType)) {
                continue;
            }

            $enabled[] = $capability;
        }

        return $this->enabled[$objectType] = $enabled;
    }

    /** Whether the administrator enabled the cell, regardless of the provider state. */
    public function isAssigned(string $objectType, string $capability): bool
    {
        $cell = $this->cells($objectType)[$capability] ?? null;

        return $cell !== null && $cell->isEnabled();
    }

    /** @return list<string> the object types the capability is assigned to, regardless of the provider state */
    public function assignedTypes(string $capability): array
    {
        $types = [];

        foreach ($this->matrix() as $objectType => $cells) {
            if (isset($cells[$capability]) && $cells[$capability]->isEnabled()) {
                $types[] = $objectType;
            }
        }

        sort($types);

        return $types;
    }

    /** @return array<string, \Icms\Capability\Config\Assignment> */
    private function cells(string $objectType): array
    {
        return $this->matrix()[$objectType] ?? [];
    }

    /** @return array<string, array<string, \Icms\Capability\Config\Assignment>> */
    private function matrix(): array
    {
        try {
            return $this->handler->matrix();
        } catch (Throwable $exception) {
            Registry::log("The capability assignments could not be read: {$exception->getMessage()}");

            return [];
        }
    }

    private function serves(string $capability, string $objectType): bool
    {
        $service = $this->registry->service($capability);

        return $service !== null && $service->supportsObjectType($objectType);
    }
}
