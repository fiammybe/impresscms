<?php

declare(strict_types=1);

namespace App\Ipf\Table;

use App\Ipf\Metadata\DefinitionRegistry;
use App\Ipf\Metadata\EntityDefinition;
use App\Ipf\Metadata\FieldDefinition;

/**
 * Lists straight from an object's description (design D5).
 *
 *     $table = $tables->create($handler);   // or ->forObject('blog', 'post')
 *     $html = $renderer->render($table, $request);
 *
 * By default a list shows the short stored fields other than the key, and its
 * quick search covers the text fields among them.
 */
final class TableFactory
{
    public function __construct(private readonly DefinitionRegistry $definitions)
    {
    }

    /**
     * The list of the object an IPF handler manages.
     */
    public function create(object $handler): TableConfig
    {
        return self::withDefaults(new TableConfig($this->definitions->forHandler($handler), $handler));
    }

    /**
     * The list of an object by name, whatever declares it.
     */
    public function forObject(string $module, string $item): TableConfig
    {
        return self::withDefaults(new TableConfig($this->definitions->get($module, $item)));
    }

    private static function withDefaults(TableConfig $table): TableConfig
    {
        $definition = $table->definition;
        $search = [];
        foreach ($definition->persistentFields() as $field) {
            if (!self::isListed($definition, $field)) {
                continue;
            }

            $table->addColumn($field->name);
            if ($field->dataType === XOBJ_DTYPE_TXTBOX) {
                $search[] = $field->name;
            }
        }

        return $table->setQuickSearch($search);
    }

    private static function isListed(EntityDefinition $definition, FieldDefinition $field): bool
    {
        if ($field->name === $definition->keyField) {
            return false;
        }

        return in_array($field->dataType, [
            XOBJ_DTYPE_TXTBOX,
            XOBJ_DTYPE_INT,
            XOBJ_DTYPE_URL,
            XOBJ_DTYPE_EMAIL,
            XOBJ_DTYPE_FLOAT,
            XOBJ_DTYPE_CURRENCY,
        ], true);
    }
}
