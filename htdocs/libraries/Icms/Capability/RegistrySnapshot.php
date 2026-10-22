<?php

/**
 * The compiled capability registry
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		ICMS\Capability
 */

declare(strict_types=1);

namespace Icms\Capability;

/**
 * What Compiler found in the installed, active modules (design D6): the
 * providers of every capability, the rejected provider files, the IPF object
 * types and the consumer declarations. It holds no administrator decisions;
 * those live in the system tables (design D7).
 *
 * @phpstan-type Provider array{
 *     module: string,
 *     file: string,
 *     class: string,
 *     version: string,
 *     compatible: bool,
 *     reason: string|null,
 *     ui: list<class-string>
 * }
 * @phpstan-type Rejection array{
 *     file: string,
 *     reason: string
 * }
 * @phpstan-type Support array{
 *     default: list<string>,
 *     unsupported: list<string>
 * }
 * @phpstan-type Data array{
 *     built: int,
 *     modules: array<string, array{name: string, version: string}>,
 *     providers: array<string, array<string, Provider>>,
 *     rejected: array<string, list<Rejection>>,
 *     objectTypes: list<string>,
 *     support: array<string, Support>
 * }
 */
final class RegistrySnapshot
{
    /** @param Data $data */
    public function __construct(private readonly array $data)
    {
    }

    public static function empty(): self
    {
        return new self([
            'built' => 0,
            'modules' => [],
            'providers' => [],
            'rejected' => [],
            'objectTypes' => [],
            'support' => [],
        ]);
    }

    /** @return Data */
    public function toArray(): array
    {
        return $this->data;
    }

    public function built(): int
    {
        return $this->data['built'];
    }

    /** @return list<string> the dirnames of the installed, active modules at build time */
    public function modules(): array
    {
        return array_keys($this->data['modules']);
    }

    public function moduleName(string $dirname): string
    {
        return $this->data['modules'][$dirname]['name'] ?? $dirname;
    }

    public function moduleVersion(string $dirname): string
    {
        return $this->data['modules'][$dirname]['version'] ?? '';
    }

    /**
     * Every provider of the capability, compatible or not, by module dirname.
     *
     * @return array<string, Provider>
     */
    public function providers(string $capability): array
    {
        return $this->data['providers'][$capability] ?? [];
    }

    /** @return Provider|null */
    public function provider(string $capability, string $module): ?array
    {
        return $this->data['providers'][$capability][$module] ?? null;
    }

    /** @return list<string> the modules that provide the capability with a compatible contract version */
    public function compatibleProviders(string $capability): array
    {
        $modules = [];

        foreach ($this->providers($capability) as $module => $provider) {
            if ($provider['compatible']) {
                $modules[] = $module;
            }
        }

        return $modules;
    }

    /** @return list<string> the capabilities the module provides */
    public function capabilitiesOf(string $module): array
    {
        $capabilities = [];

        foreach ($this->data['providers'] as $capability => $providers) {
            if (isset($providers[$module])) {
                $capabilities[] = $capability;
            }
        }

        return $capabilities;
    }

    /** @return array<string, list<Rejection>> rejected provider files by module dirname */
    public function rejected(): array
    {
        return $this->data['rejected'];
    }

    /** @return list<string> the IPF object types ("news/article") of the installed, active modules */
    public function objectTypes(): array
    {
        return $this->data['objectTypes'];
    }

    /** @return list<string> */
    public function defaultsFor(string $objectType): array
    {
        return $this->data['support'][$objectType]['default'] ?? [];
    }

    public function isUnsupported(string $objectType, string $capability): bool
    {
        return in_array($capability, $this->data['support'][$objectType]['unsupported'] ?? [], true);
    }
}
