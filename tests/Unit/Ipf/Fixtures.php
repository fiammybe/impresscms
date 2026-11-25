<?php

declare(strict_types=1);

namespace App\Tests\Unit\Ipf;

use Icms\Ipf\Metadata\LegacyOperations;
use Icms\Ipf\Metadata\ListColumn;
use Icms\Ipf\Metadata\ListView;
use Icms\Ipf\Metadata\Relation;

/**
 * Stand-ins for an IPF handler and entity: the properties and methods the
 * definition reader uses, without the legacy runtime.
 */
#[ListView(quickSearch: ['name', 'extension'], createButton: true)]
#[ListColumn('name', width: 150)]
#[ListColumn('extension')]
#[LegacyOperations('/modules/system/admin.php', match: ['fct' => 'fixture'], form: 'mod', save: 'addfixture', delete: 'del')]
class FixtureHandler
{
    public string $keyName = 'fixtureid';
    public string $_moduleName = 'system';
    public string $_itemname = 'fixture';
    public string $table = 'icms_system_fixture';
    public string $identifierName = 'fixtureid';
    public string $summaryName = 'name';

    /** @var list<array<string, string>> */
    public array $permissions = [['perm_name' => 'use_fixture', 'caption' => 'Use', 'description' => 'Who may use it']];

    /**
     * @return list<array<string, string>>
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }
}

#[Relation('parentid', 'system.fixture', ownership: 'reference')]
final class RelatedFixtureHandler extends FixtureHandler
{
}

#[ListView(quickSearch: ['nosuchfield'])]
final class BrokenListFixtureHandler extends FixtureHandler
{
}

final class FixtureEntity
{
    /** @var array<string, array<string, mixed>> */
    public array $vars = [];

    /** @var array<string, array<string, mixed>> */
    public array $controls = [];

    public function __construct()
    {
        $this->initVar('fixtureid', 3, null, true);
        $this->initVar('extension', 1, null, true, null, 'Extension', 'The extension');
        $this->initVar('types', 2, null, true, null, 'Types');
        $this->initVar('name', 1, null, true, 60, 'Name');
        $this->initVar('dirname', 101, null, true, null, 'Modules');
        $this->initVar('parentid', 3, 0, false, null, 'Parent');
        $this->initVar('settings', 6, null, false, null, 'Settings');
        $this->controls['dirname'] = ['name' => 'selectmulti', 'itemHandler' => 'icms_module', 'method' => 'getActive'];
        $this->controls['parentid'] = ['itemHandler' => 'fixture', 'module' => 'system', 'method' => 'getList'];
    }

    public function initVar(string $key, int $type, mixed $value = null, bool $required = false, ?int $maxlength = null, string $caption = '', string $description = ''): void
    {
        $this->vars[$key] = [
            'value' => $value, 'required' => $required, 'data_type' => $type, 'maxlength' => $maxlength, 'changed' => false,
            'options' => '', 'multilingual' => false, 'form_caption' => $caption, 'form_dsc' => $description, 'sortby' => false,
            'persistent' => true, 'displayOnForm' => true, 'displayOnSingleView' => true, 'readonly' => false,
        ];
    }
}
