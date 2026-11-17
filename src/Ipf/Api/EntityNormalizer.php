<?php

declare(strict_types=1);

namespace App\Ipf\Api;

use App\Ipf\IpfRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * An object as the API returns it (design D6): its id, title and address,
 * and the exposed fields with typed, unformatted values. Only stored fields
 * are returned, and only those of the resource's whitelist when it has one.
 * Nothing is formatted or sanitised for HTML: consumers escape.
 */
final class EntityNormalizer implements NormalizerInterface
{
    public function __construct(private readonly IpfRepository $repository)
    {
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array{id: mixed, title: string, url: ?string, fields: array<string, mixed>}
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        if (!$data instanceof ApiItem) {
            throw new \InvalidArgumentException('EntityNormalizer normalizes API items only.');
        }

        $definition = $data->definition;
        $store = $this->repository->store($definition);
        $fields = [];
        foreach ($definition->persistentFields() as $name => $field) {
            if ($data->resource->fields !== null && !in_array($name, $data->resource->fields, true)) {
                continue;
            }

            $fields[$name] = $store->value($definition, $data->object, $name);
        }

        $title = $store->value($definition, $data->object, $definition->identifierField);

        return [
            'id' => $store->value($definition, $data->object, $definition->keyField),
            'title' => is_scalar($title) ? (string) $title : '',
            'url' => $store->url($definition, $data->object),
            'fields' => $fields,
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof ApiItem;
    }

    /**
     * @return array<class-string, bool>
     */
    public function getSupportedTypes(?string $format): array
    {
        return [ApiItem::class => true];
    }
}
