<?php

declare(strict_types=1);

namespace App\Ipf\Metadata;

use App\Ipf\Attribute\IpfObject;
use App\Ipf\Store\Dbal\PipeArrayType;
use App\Legacy\LegacyRuntime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Descriptions of ORM-mapped classes marked with #[IpfObject] (design D7):
 * the same description an IPF object yields, read from the ORM mapping.
 *
 * Field names, types, lengths and the key come from the mapping, a column
 * that is not nullable is required, and captions come from language
 * constants by the IPF convention. Associations are not fields.
 */
final class DoctrineDefinitionProvider implements DefinitionProviderInterface
{
    /** @var array<string, array{ClassMetadata<object>, IpfObject}>|null by "module/item" */
    private ?array $objects = null;

    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly LegacyRuntime $runtime,
    ) {
    }

    public function definition(string $module, string $item): ?EntityDefinition
    {
        [$metadata, $object] = $this->objects()["{$module}/{$item}"] ?? [null, null];
        if ($metadata === null || $object === null) {
            return null;
        }

        // the data type constants and the language files belong to ImpressCMS
        $this->runtime->bootTo('module');
        class_exists(\Icms\Core\Entity::class);
        icms_loadLanguageFile($object->module, $object->language ?? 'common', $object->adminLanguage);

        $prefix = $object->captionPrefix ?? strtoupper("_CO_{$object->module}_{$object->item}_");
        $fields = [];
        foreach ($metadata->fieldMappings as $name => $mapping) {
            $name = (string) $name;
            $caption = strtoupper($prefix . $name);
            $fields[$name] = new FieldDefinition(
                name: $name,
                dataType: self::dataType((string) $mapping->type),
                required: !$mapping->nullable,
                maxLength: $mapping->length,
                caption: defined($caption) ? trim(strip_tags((string) constant($caption))) : $name,
                description: defined("{$caption}_DSC") ? (string) constant("{$caption}_DSC") : '',
            );
        }

        $key = $metadata->getSingleIdentifierFieldName();

        return new EntityDefinition(
            module: $object->module,
            item: $object->item,
            table: $metadata->getTableName(),
            keyField: $key,
            identifierField: $object->identifier ?? $key,
            summaryField: $object->summary,
            fields: $fields,
            permissions: [],
            source: EntityDefinition::SOURCE_ORM,
            className: $metadata->getName(),
        );
    }

    /**
     * @return array<string, array{ClassMetadata<object>, IpfObject}>
     */
    private function objects(): array
    {
        if ($this->objects !== null) {
            return $this->objects;
        }

        $this->objects = [];
        foreach ($this->doctrine->getManagers() as $manager) {
            if (!$manager instanceof EntityManagerInterface) {
                continue;
            }

            foreach ($manager->getMetadataFactory()->getAllMetadata() as $metadata) {
                $attributes = $metadata->getReflectionClass()->getAttributes(IpfObject::class);
                if ($attributes === []) {
                    continue;
                }

                $object = $attributes[0]->newInstance();
                $this->objects["{$object->module}/{$object->item}"] = [$metadata, $object];
            }
        }

        return $this->objects;
    }

    private static function dataType(string $type): int
    {
        return match ($type) {
            Types::STRING, Types::ASCII_STRING, Types::GUID => XOBJ_DTYPE_TXTBOX,
            Types::TEXT => XOBJ_DTYPE_TXTAREA,
            Types::INTEGER, Types::SMALLINT, Types::BIGINT, Types::BOOLEAN => XOBJ_DTYPE_INT,
            Types::FLOAT, Types::DECIMAL => XOBJ_DTYPE_FLOAT,
            Types::SIMPLE_ARRAY, PipeArrayType::NAME => XOBJ_DTYPE_SIMPLE_ARRAY,
            Types::JSON => XOBJ_DTYPE_ARRAY,
            default => XOBJ_DTYPE_OTHER,
        };
    }
}
