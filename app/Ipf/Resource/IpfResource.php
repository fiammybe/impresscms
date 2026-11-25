<?php

declare(strict_types=1);

namespace App\Ipf\Resource;

use App\Ipf\Repository\IpfResourceRepository;
use Illuminate\Contracts\Auth\Access\Gate;

/**
 * What a resource controller needs of one IPF resource, chosen by the
 * registration's switches: the repository (legacy handler or Eloquent reads),
 * the generated descriptor, the language texts of the live definition, and
 * authorization through the generated policy (or none beyond the legacy
 * middleware, when the policy is switched back to legacy).
 *
 * Injected with #[ForResource('<module>.<item>')].
 */
final class IpfResource
{
    /**
     * @param class-string<ResourceDescriptor> $descriptor
     */
    public function __construct(
        public readonly ResourceRegistration $registration,
        public readonly string $descriptor,
        public readonly IpfResourceRepository $repository,
        public readonly LegacyResource $legacy,
        private readonly Gate $gate,
    ) {
    }

    public function key(): string
    {
        return $this->registration->key();
    }

    /**
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function authorize(string $ability, mixed ...$arguments): void
    {
        if ($this->registration->policy() === ResourceRegistration::POLICY_LEGACY) {
            return;
        }

        $this->gate->authorize($ability, [$this->registration->generatedClass(), ...$arguments]);
    }

    public function rendersWithBlade(): bool
    {
        return $this->registration->renderer() === ResourceRegistration::RENDERER_BLADE;
    }

    public function caption(string $name): string
    {
        return $this->legacy->caption($name);
    }

    public function text(string $constant): string
    {
        return $this->legacy->text($constant);
    }
}
