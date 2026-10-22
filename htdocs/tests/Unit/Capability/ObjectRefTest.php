<?php

/**
 * Object references (design D4)
 *
 * @package icms\tests
 */

declare(strict_types=1);

namespace Icms\Tests\Unit\Capability;

use Icms\Capability\Exception\UnsupportedObjectException;
use Icms\Capability\ObjectRef;
use Icms\Ipf\Entity;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use stdClass;

#[Group('spec:capability-contracts')]
final class ObjectRefTest extends TestCase
{
    /**
     * @scenario Reference from an IPF object
     */
    public function testReferenceFromAnIpfObjectEqualsOneBuiltFromItsValues(): void
    {
        $ref = ObjectRef::fromEntity(self::entity('news', 'article', 42));

        self::assertSame('news/article/42', $ref->toString());
        self::assertSame('news/article', $ref->objectType());
        self::assertTrue($ref->equals(new ObjectRef('news', 'article', 42)));
        self::assertTrue($ref->equals(ObjectRef::fromString('news/article/42')));
        self::assertFalse($ref->equals(new ObjectRef('news', 'article', 43)));
    }

    /**
     * @scenario Non-IPF object
     */
    public function testNonIpfObjectsAreRejected(): void
    {
        $this->expectException(UnsupportedObjectException::class);
        $this->expectExceptionMessage('Only IPF objects');

        ObjectRef::fromEntity(new stdClass());
    }

    /**
     * @scenario New, unsaved object
     */
    public function testUnsavedObjectsCannotBeReferenced(): void
    {
        $this->expectException(UnsupportedObjectException::class);
        $this->expectExceptionMessage('no key value yet');

        ObjectRef::fromEntity(self::entity('news', 'article', 0));
    }

    public function testTheTypeOfAnUnsavedObjectIsKnown(): void
    {
        self::assertSame('news/article', ObjectRef::typeOf(self::entity('news', 'article', 0)));
    }

    public function testObjectsWithACompositeKeyAreRejected(): void
    {
        $entity = self::entity('news', 'link', 1);
        $entity->handler->keyName = ['article_id', 'tag_id'];

        $this->expectException(UnsupportedObjectException::class);
        $this->expectExceptionMessage('composite key');

        ObjectRef::fromEntity($entity);
    }

    public function testMalformedReferencesAreRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ObjectRef::fromString('news/article');
    }

    public function testReferencesNeedAPositiveId(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ObjectRef('news', 'article', 0);
    }

    private static function entity(string $module, string $item, int $id): Entity
    {
        $handler = new stdClass();
        $handler->_moduleName = $module;
        $handler->_itemname = $item;
        $handler->keyName = "{$item}_id";

        return new class ($handler, $id) extends Entity {
            public function __construct(&$handler, private readonly int $fixedId)
            {
                parent::__construct($handler);
            }

            public function id()
            {
                return $this->fixedId;
            }
        };
    }
}
