<?php
/**
 * An IPF entity in memory: the metadata and methods the IPF form reads.
 *
 * @package icms\tests
 */

declare(strict_types=1);

namespace Icms\Tests\Support;

final class FakeIpfEntity
{
    /** @var array<string, array<string, mixed>> */
    public array $vars = [];

    /** @var array<string, array<string, mixed>> */
    public array $controls = [];

    public object $handler;

    /**
     * @param array<string, array<string, mixed>> $vars     key => partial variable definition
     * @param array<string, array<string, mixed>> $controls
     * @param list<array<string, mixed>>          $permissions handler permissions (perm_name, caption, description)
     * @param array<string, mixed>                $moduleConfig
     * @param array<string, list<int>>            $groupPerms  permission => group ids
     */
    public function __construct(
        array $vars,
        array $controls = [],
        private bool $new = false,
        array $permissions = [],
        array $moduleConfig = [],
        private array $groupPerms = [],
        string $keyName = 'id',
    ) {
        foreach ($vars as $key => $var) {
            $this->vars[$key] = $var + [
                'value' => null,
                'data_type' => XOBJ_DTYPE_TXTBOX,
                'required' => false,
                'maxlength' => null,
                'form_caption' => ucfirst($key),
                'form_dsc' => '',
                'displayOnForm' => true,
                'readonly' => false,
            ];
        }
        $this->controls = $controls;
        $this->handler = new class ($keyName, $permissions, $moduleConfig) {
            public string $_moduleName = 'fake';
            public string $_modulePath = '/tmp/fake';

            /**
             * @param list<array<string, mixed>> $permissions
             * @param array<string, mixed>       $moduleConfig
             */
            public function __construct(public string $keyName, private array $permissions, private array $moduleConfig)
            {
            }

            /** @return list<array<string, mixed>>|false */
            public function getPermissions(): array|false
            {
                return $this->permissions ?: false;
            }

            /** @return array<string, mixed> */
            public function getModuleConfig(): array
            {
                return $this->moduleConfig;
            }

            /** @return array<int, string> */
            public function getColours(): array
            {
                return ['red' => 'Red', 'green' => 'Green & <b>blue</b>'];
            }
        };
    }

    public function getVar(string $key, string $format = 's'): mixed
    {
        $value = $this->vars[$key]['value'] ?? null;
        if ($format === 'e' && is_string($value)) {
            return htmlspecialchars($value, ENT_QUOTES);
        }

        return $value;
    }

    /** @return array<string, mixed>|false */
    public function getControl(string $key): array|false
    {
        return $this->controls[$key] ?? false;
    }

    /** @param array<string, mixed> $options */
    public function setControl(string $key, array $options): void
    {
        $this->controls[$key] = $options;
    }

    public function isNew(): bool
    {
        return $this->new;
    }

    /** @return list<int> */
    public function getGroupPerm(string $name): array
    {
        return $this->groupPerms[$name] ?? [];
    }

    public function getUploadDir(): string
    {
        return 'http://localhost/uploads/fake/';
    }

    public function getImageDir(): string
    {
        return 'http://localhost/uploads/fake/images/';
    }
}
