<?php

declare(strict_types=1);

namespace App\Ipf\Example;

use App\Ipf\Attribute\IpfObject;
use App\Ipf\Store\Dbal\PipeArrayType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Example and test fixture: the system module's mimetype table as a plain
 * ORM-mapped class (design D7), known as the object `system/mimetype_orm`.
 *
 * It shows what a module that prefers the ORM writes to get lists, filters
 * and the API, and lets the tests compare both declarations of one table. It
 * is mapped in the test environment only and is read-only: the mimetypes
 * belong to the IPF handler.
 */
#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: 'system_mimetype')]
#[IpfObject(
    module: 'system',
    item: 'mimetype_orm',
    summary: 'name',
    captionPrefix: '_CO_ICMS_MIMETYPE_',
    language: 'mimetype',
    adminLanguage: true,
)]
class Mimetype
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $mimetypeid = 0;

    #[ORM\Column(type: Types::STRING, length: 60)]
    private string $extension = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $types = '';

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $name = '';

    /** @var list<string> */
    #[ORM\Column(type: PipeArrayType::NAME)]
    private array $dirname = [];

    public function getMimetypeid(): int
    {
        return $this->mimetypeid;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function getTypes(): string
    {
        return $this->types;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return list<string>
     */
    public function getDirname(): array
    {
        return $this->dirname;
    }
}
