<?php

/**
 * Catalogue of the capabilities the core knows
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		ICMS\Capability
 */

declare(strict_types=1);

namespace Icms\Capability;

use Icms\Capability\Contract\Tagging\CapabilityInterface as Tagging;
use Icms\Capability\Contract\Taxonomy\CapabilityInterface as Taxonomy;
use InvalidArgumentException;

/**
 * Contracts live in the core, never in a provider module (spec
 * capability-contracts), so every capability is listed here. A new capability
 * is a new contract plus an entry in this catalogue.
 */
final class Definitions
{
    /** @var array<string, array{
        contract: class-string,
        title: string
    }> */
    private const CATALOGUE = [
        Tagging::NAME => [
            'contract' => Tagging::class,
            'title' => '_CAPABILITY_TAGGING',
        ],
        Taxonomy::NAME => [
            'contract' => Taxonomy::class,
            'title' => '_CAPABILITY_TAXONOMY',
        ],
    ];

    /** @return list<string> */
    public static function names(): array
    {
        return array_keys(self::CATALOGUE);
    }

    public static function has(string $name): bool
    {
        return isset(self::CATALOGUE[$name]);
    }

    /** @return class-string */
    public static function contract(string $name): string
    {
        return self::definition($name)['contract'];
    }

    /** The contract version of the core ("1.0"). */
    public static function version(string $name): string
    {
        return constant(self::contract($name) . '::VERSION');
    }

    public static function title(string $name): string
    {
        $constant = self::definition($name)['title'];

        if (! defined($constant) && function_exists('icms_loadLanguageFile')) {
            icms_loadLanguageFile('core', 'capability');
        }

        return defined($constant) ? constant($constant) : ucfirst($name);
    }

    /**
     * Why a provider's contract version cannot be used with the core's, or null
     * when it can: same major version, and a provider minor version equal to or
     * greater than the core's.
     */
    public static function incompatibility(string $coreVersion, string $providerVersion): ?string
    {
        $core = self::parse($coreVersion);
        $provider = self::parse($providerVersion);

        if ($provider === null) {
            return "\"{$providerVersion}\" is not a contract version (major.minor)";
        }

        if ($core === null) {
            return "\"{$coreVersion}\" is not a contract version (major.minor)";
        }

        if ($provider[0] !== $core[0]) {
            return "major version {$provider[0]} ≠ {$core[0]}";
        }

        if ($provider[1] < $core[1]) {
            return "minor version {$provider[1]} < {$core[1]}";
        }

        return null;
    }

    public static function isCompatible(string $coreVersion, string $providerVersion): bool
    {
        return self::incompatibility($coreVersion, $providerVersion) === null;
    }

    /** @return array{
        contract: class-string,
        title: string
    } */
    private static function definition(string $name): array
    {
        if (! isset(self::CATALOGUE[$name])) {
            throw new InvalidArgumentException("Unknown capability \"{$name}\".");
        }

        return self::CATALOGUE[$name];
    }

    /** @return array{0: int, 1: int}|null */
    private static function parse(string $version): ?array
    {
        if (! preg_match('/^(\d+)\.(\d+)(?:\.\d+)?$/', $version, $matches)) {
            return null;
        }

        return [(int) $matches[1], (int) $matches[2]];
    }
}
