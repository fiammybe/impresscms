<?php

declare(strict_types=1);

namespace App\Ipf\Store;

use App\Ipf\Attribute\IpfObject;
use App\Legacy\LegacyRuntime;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;

/**
 * Gives the tables of ORM-mapped module objects the site's table prefix
 * (design D7): a class names its table as a module would (`blog_post`), the
 * site's mainfile.php decides the prefix (`icms_blog_post`).
 */
#[AsDoctrineListener(event: Events::loadClassMetadata)]
final class TablePrefixListener
{
    public function __construct(private readonly LegacyRuntime $runtime)
    {
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event): void
    {
        $metadata = $event->getClassMetadata();
        if ($metadata->getReflectionClass()->getAttributes(IpfObject::class) === []) {
            return;
        }

        $this->runtime->bootTo('constants');
        $prefix = defined('XOOPS_DB_PREFIX') ? (string) XOOPS_DB_PREFIX : '';
        if ($prefix === '') {
            return;
        }

        $metadata->setPrimaryTable(['name' => "{$prefix}_{$metadata->getTableName()}"]);
    }
}
