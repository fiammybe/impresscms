<?php

declare(strict_types=1);

namespace App\Ipf\Resource;

use OutOfBoundsException;

/**
 * The IPF resources the active modules registered (App\Module), by key
 * `<module>.<item>`. The compiler generates their artifacts; the host serves
 * them with the switches of each registration.
 */
final class ResourceRegistry
{
    /** @var array<string, ResourceRegistration> */
    private array $registrations = [];

    public function add(ResourceRegistration $registration): void
    {
        $this->registrations[$registration->key()] = $registration;
    }

    public function get(string $key): ResourceRegistration
    {
        return $this->registrations[$key] ?? throw new OutOfBoundsException("No module registered the IPF resource \"{$key}\".");
    }

    public function has(string $key): bool
    {
        return isset($this->registrations[$key]);
    }

    /**
     * @return array<string, ResourceRegistration>
     */
    public function all(?string $module = null): array
    {
        $all = $this->registrations;
        ksort($all);

        return $module === null ? $all : array_filter($all, static fn (ResourceRegistration $registration): bool => $registration->module === $module);
    }
}
