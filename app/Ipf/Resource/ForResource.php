<?php

declare(strict_types=1);

namespace App\Ipf\Resource;

use Attribute;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Container\ContextualAttribute;

/**
 * Injects the IpfResource of a registered IPF resource:
 *
 *     public function __construct(#[ForResource('system.mimetype')] private readonly IpfResource $mimetypes)
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class ForResource implements ContextualAttribute
{
    public function __construct(public readonly string $key)
    {
    }

    public static function resolve(self $attribute, Container $container): IpfResource
    {
        return $container->make(IpfResourceFactory::class)->get($attribute->key);
    }
}
