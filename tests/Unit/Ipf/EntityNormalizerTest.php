<?php

declare(strict_types=1);

namespace App\Tests\Unit\Ipf;

use App\Ipf\Api\ApiItem;
use App\Ipf\Api\ApiResource;
use App\Ipf\Api\EntityNormalizer;
use App\Ipf\IpfRepository;
use App\Ipf\Metadata\EntityDefinition;
use App\Ipf\Metadata\FieldDefinition;
use App\Ipf\Query\ListQuery;
use App\Ipf\Store\ListResult;
use App\Ipf\Store\ObjectStoreInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * What the API returns of an object: stored fields only, and only the
 * whitelisted ones when the resource has a whitelist.
 */
#[Group('spec:ipf-api')]
final class EntityNormalizerTest extends TestCase
{
    private const TXTBOX = 1;
    private const INT = 3;

    /**
     * @scenario Field whitelist
     */
    public function testWhitelistLimitsTheFields(): void
    {
        $item = self::normalize(['title', 'no_such_field']);

        self::assertSame(['id' => 7, 'title' => 'Seven', 'url' => '/post.php?post_id=7', 'fields' => ['title' => 'Seven']], $item);
    }

    public function testWithoutWhitelistEveryStoredFieldIsReturned(): void
    {
        $item = self::normalize(null);

        self::assertSame(['post_id' => 7, 'title' => 'Seven', 'secret' => 'hidden'], $item['fields']);
        self::assertArrayNotHasKey('preview', $item['fields'], 'a field that is not stored');
    }

    /**
     * @param list<string>|null $fields
     *
     * @return array<string, mixed>
     */
    private static function normalize(?array $fields): array
    {
        $definition = new EntityDefinition('blog', 'post', 'icms_blog_post', 'post_id', 'title', null, [
            'post_id' => new FieldDefinition('post_id', self::INT),
            'title' => new FieldDefinition('title', self::TXTBOX),
            'secret' => new FieldDefinition('secret', self::TXTBOX),
            'preview' => new FieldDefinition('preview', self::TXTBOX, persistent: false),
        ]);
        $store = new class () implements ObjectStoreInterface {
            public function supports(EntityDefinition $definition): bool
            {
                return true;
            }

            public function find(EntityDefinition $definition, int|string $id): ?object
            {
                return null;
            }

            public function list(EntityDefinition $definition, ListQuery $query): ListResult
            {
                return new ListResult([], 0);
            }

            public function value(EntityDefinition $definition, object $object, string $field): mixed
            {
                return $object->{$field};
            }

            public function display(EntityDefinition $definition, object $object, string $field): string
            {
                return (string) $object->{$field};
            }

            public function url(EntityDefinition $definition, object $object): ?string
            {
                return '/post.php?post_id=' . $object->post_id;
            }
        };
        $object = (object) ['post_id' => 7, 'title' => 'Seven', 'secret' => 'hidden', 'preview' => 'Sev…'];

        return (new EntityNormalizer(new IpfRepository([$store])))
            ->normalize(new ApiItem($definition, new ApiResource('blog', 'post', 'ROLE_USER', [], $fields), $object));
    }
}
