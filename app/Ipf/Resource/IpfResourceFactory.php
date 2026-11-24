<?php

declare(strict_types=1);

namespace App\Ipf\Resource;

use App\Ipf\Repository\EloquentReadRepository;
use App\Ipf\Repository\LegacyHandlerRepository;
use App\Legacy\HandlerLocator;
use App\Legacy\LegacyRuntime;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Database\DatabaseManager;
use LogicException;

/**
 * Builds the IpfResource of a registered resource, once per request, from its
 * registration's switches and its generated classes.
 */
final class IpfResourceFactory
{
    /** @var array<string, IpfResource> */
    private array $resources = [];

    public function __construct(
        private readonly ResourceRegistry $registry,
        private readonly HandlerLocator $handlers,
        private readonly LegacyRuntime $runtime,
        private readonly Gate $gate,
        private readonly DatabaseManager $db,
    ) {
    }

    public function get(string $key): IpfResource
    {
        return $this->resources[$key] ??= $this->make($this->registry->get($key));
    }

    private function make(ResourceRegistration $registration): IpfResource
    {
        /** @var class-string<ResourceDescriptor> $descriptor */
        $descriptor = $registration->generatedClass('Descriptor');
        if (! class_exists($descriptor)) {
            throw new LogicException("{$registration->key()} is not compiled: run `php artisan icms:definitions:compile`.");
        }

        if ($registration->renderer() === ResourceRegistration::RENDERER_BLADE && ! $descriptor::supportsBlade()) {
            $unsupported = implode(', ', array_keys($descriptor::data()['support']['unsupported']['blade']));
            throw new LogicException("{$registration->key()} has controls without Blade components ({$unsupported}): set its renderer to legacy.");
        }

        $legacy = new LegacyResource($registration, $descriptor, $this->handlers, $this->runtime);
        $repository = new LegacyHandlerRepository($legacy);
        if ($registration->persistence() === ResourceRegistration::PERSISTENCE_ELOQUENT_READ) {
            $repository = new EloquentReadRepository($repository, $registration->generatedClass(), $descriptor, $this->db->connection('icms'));
        }

        return new IpfResource($registration, $descriptor, $repository, $legacy, $this->gate);
    }
}
