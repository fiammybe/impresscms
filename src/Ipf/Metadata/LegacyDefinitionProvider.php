<?php

declare(strict_types=1);

namespace App\Ipf\Metadata;

use App\Ipf\Form\ControlMap;
use App\Legacy\ModuleHandlerLocator;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

/**
 * Descriptions of IPF objects, read from the definition their class already
 * declares (design D2).
 *
 * The object declares its variables in its constructor, so one blank object
 * from the handler carries the whole definition, as Icms\Ipf\View\Table reads
 * it. Nothing is stored and the handler is not changed.
 */
#[AsTaggedItem(priority: 10)]
final class LegacyDefinitionProvider implements DefinitionProviderInterface
{
    public function __construct(private readonly ModuleHandlerLocator $handlers)
    {
    }

    public function definition(string $module, string $item): ?EntityDefinition
    {
        if (!$this->handlers->has($module, $item)) {
            return null;
        }

        $handler = $this->handlers->get($module, $item);
        if (!self::isIpfHandler($handler)) {
            return null;
        }

        return self::fromHandler($handler);
    }

    /**
     * Whether a handler is an IPF handler with a single key field: the objects
     * this layer can describe.
     */
    public static function isIpfHandler(object $handler): bool
    {
        if (!isset($handler->_itemname, $handler->keyName, $handler->table)) {
            return false;
        }

        return is_string($handler->keyName) && $handler->keyName !== '';
    }

    public static function fromHandler(object $handler): EntityDefinition
    {
        if (!self::isIpfHandler($handler)) {
            throw new \InvalidArgumentException('An object description needs an IPF handler with a single key field.');
        }

        $blank = $handler->create();
        $fields = [];
        foreach ($blank->vars as $key => $var) {
            $key = (string) $key;
            $fields[$key] = self::field($key, $var, $blank->controls[$key] ?? null);
        }

        $permissions = [];
        foreach ((array) $handler->getPermissions() as $permission) {
            $permissions[] = [
                'name' => (string) $permission['perm_name'],
                'caption' => (string) ($permission['caption'] ?? ''),
                'description' => (string) (($permission['description'] ?? '') ?: ''),
            ];
        }

        $summary = is_string($handler->summaryName) && $handler->summaryName !== '' ? $handler->summaryName : null;

        return new EntityDefinition(
            module: (string) $handler->_moduleName,
            item: (string) $handler->_itemname,
            table: (string) $handler->table,
            keyField: $handler->keyName,
            identifierField: (string) $handler->identifierName ?: $handler->keyName,
            summaryField: $summary,
            fields: $fields,
            permissions: $permissions,
            source: EntityDefinition::SOURCE_IPF,
            // the name Icms\Ipf\View\Table uses as the table's id
            className: (string) $handler->className,
        );
    }

    /**
     * @param array<string, mixed> $var
     */
    private static function field(string $key, array $var, mixed $control): FieldDefinition
    {
        $dataType = (int) $var['data_type'];
        $isSection = in_array($dataType, [XOBJ_DTYPE_FORM_SECTION, XOBJ_DTYPE_FORM_SECTION_CLOSE], true);
        $maxLength = isset($var['maxlength']) && (int) $var['maxlength'] > 0 ? (int) $var['maxlength'] : null;

        return new FieldDefinition(
            name: $key,
            dataType: $dataType,
            required: !empty($var['required']),
            maxLength: $maxLength,
            caption: ControlMap::caption($key, $var),
            description: (string) ($var['form_dsc'] ?? ''),
            control: is_array($control) ? $control : null,
            persistent: !$isSection && ($var['persistent'] ?? true),
            sortable: !$isSection,
            multilingual: !empty($var['multilingual']),
            displayOnForm: (bool) ($var['displayOnForm'] ?? true),
            readOnly: !empty($var['readonly']),
        );
    }
}
