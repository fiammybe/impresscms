<?php

/**
 * Discovers capability providers by naming convention
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		ICMS\Capability
 */

declare(strict_types=1);

namespace Icms\Capability;

use ErrorException;
use Icms\Capability\Contract\BaseInterface;
use Icms\Capability\Ui\FormContributorInterface;
use Icms\Capability\Ui\PersistenceContributorInterface;
use Icms\Capability\Ui\TableFilterContributorInterface;
use Icms\Capability\Ui\TemplateRendererInterface;
use Icms\Capability\Ui\ViewContributorInterface;
use ReflectionClass;
use Throwable;

/**
 * Scans `modules/<dirname>/capability/<Name>.php` of the installed, active
 * modules and builds the registry snapshot (design D5, D6). The file name is
 * the capability ("Tagging.php" → tagging), the class is
 * mod_<dirname>_<Name>Capability, and the contracts the class implements say
 * what it offers. Only the module lifecycle and the administrator run this;
 * ordinary requests read the written snapshot.
 *
 * Errors that PHP raises while compiling a class (an incomplete implementation
 * of an interface, a class declared twice) end the request and cannot be
 * reported per file.
 *
 * @phpstan-import-type Data from RegistrySnapshot
 * @phpstan-type Module array{
 *     name: string,
 *     version: string,
 *     objectItems: list<string>,
 *     support: array<string, mixed>
 * }
 */
final class Compiler
{
    public const UI_CONTRACTS = [
        FormContributorInterface::class,
        PersistenceContributorInterface::class,
        ViewContributorInterface::class,
        TableFilterContributorInterface::class,
        TemplateRendererInterface::class,
    ];

    /** The framework's own configuration items, which capabilities cannot be assigned to. */
    public const CONFIGURATION_TYPES = ['system/capabilityassignment', 'system/capabilityprovider'];

    private const DIRECTORY = 'capability';

    public function __construct(private readonly string $modulesPath)
    {
    }

    public static function forSite(): self
    {
        return new self(ICMS_ROOT_PATH . '/modules');
    }

    /**
     * The installed, active modules as the compiler needs them.
     *
     * @return array<string, Module>
     */
    public static function installedModules(): array
    {
        $moduleHandler = \icms::handler('icms_module');
        $modules = [];

        foreach (array_keys(\Icms\Module\Handler::getActive()) as $dirname) {
            $module = $moduleHandler->getByDirname((string) $dirname);

            if (! is_object($module)) {
                continue;
            }

            $objectItems = $module->getInfo('object_items');
            $support = $module->getInfo('capability_support');

            $modules[(string) $dirname] = [
                'name' => (string) $module->getVar('name', 'n'),
                'version' => (string) $module->getVar('version', 'n'),
                'objectItems' => is_array($objectItems) ? array_values(array_map('strval', $objectItems)) : [],
                'support' => is_array($support) ? $support : [],
            ];
        }

        return $modules;
    }

    /** @param array<string, Module> $modules */
    public function compile(array $modules): RegistrySnapshot
    {
        $data = RegistrySnapshot::empty()->toArray();
        $data['built'] = time();

        foreach ($modules as $dirname => $module) {
            $data['modules'][$dirname] = ['name' => $module['name'], 'version' => $module['version']];

            foreach ($module['objectItems'] as $item) {
                $objectType = "{$dirname}/{$item}";

                if (in_array($objectType, self::CONFIGURATION_TYPES, true)) {
                    continue;
                }

                $data['objectTypes'][] = $objectType;
            }

            foreach ($this->support($dirname, $module) as $objectType => $support) {
                $data['support'][$objectType] = $support;
            }

            foreach ($this->providerFiles($dirname) as $file) {
                $result = $this->inspect($dirname, $file);

                if (is_string($result)) {
                    $data['rejected'][$dirname][] = ['file' => $file, 'reason' => $result];

                    continue;
                }

                $data['providers'][$result['capability']][$dirname] = $result['provider'];
            }
        }

        ksort($data['providers']);

        return new RegistrySnapshot($data);
    }

    /** @return list<string> absolute paths of the PHP files in the module's capability directory */
    public function providerFiles(string $dirname): array
    {
        $directory = "{$this->modulesPath}/{$dirname}/" . self::DIRECTORY;

        if (! is_dir($directory)) {
            return [];
        }

        $files = glob("{$directory}/*.php") ?: [];
        sort($files);

        return array_map(static fn (string $file): string => str_replace('\\', '/', $file), $files);
    }

    /**
     * The newest modification time of the capability directories and files of these modules.
     *
     * @param list<string> $dirnames
     */
    public function sourcesModifiedAt(array $dirnames): int
    {
        $newest = 0;

        foreach ($dirnames as $dirname) {
            $directory = "{$this->modulesPath}/{$dirname}/" . self::DIRECTORY;

            if (! is_dir($directory)) {
                continue;
            }

            $newest = max($newest, (int) filemtime($directory));

            foreach ($this->providerFiles($dirname) as $file) {
                $newest = max($newest, (int) filemtime($file));
            }
        }

        return $newest;
    }

    public static function className(string $dirname, string $file): string
    {
        return 'mod_' . $dirname . '_' . ucfirst(basename($file, '.php')) . 'Capability';
    }

    /**
     * @return array{
     *     capability: string,
     *     provider: array{module: string, file: string, class: string, version: string, compatible: bool, reason: string|null, ui: list<class-string>}
     * }|string the provider, or why the file was rejected
     */
    private function inspect(string $dirname, string $file): array|string
    {
        $capability = strtolower(basename($file, '.php'));

        if (! Definitions::has($capability)) {
            $known = implode(', ', Definitions::names());

            return "\"{$capability}\" is not a known capability ({$known})";
        }

        $class = self::className($dirname, $file);

        try {
            $this->load($file, $class);
        } catch (Throwable $exception) {
            return "the file could not be loaded: {$exception->getMessage()}";
        }

        if (! class_exists($class, false)) {
            return "the file does not declare the class {$class}";
        }

        $reflection = new ReflectionClass($class);
        $contract = Definitions::contract($capability);

        if (! $reflection->implementsInterface($contract)) {
            return "{$class} does not implement {$contract}";
        }

        if (! $reflection->isInstantiable()) {
            return "{$class} cannot be instantiated";
        }

        $constructor = $reflection->getConstructor();

        if ($constructor !== null && $constructor->getNumberOfRequiredParameters() > 0) {
            return "the constructor of {$class} must not require arguments";
        }

        try {
            /** @var BaseInterface $instance */
            $instance = $reflection->newInstance();
            $version = $instance->contractVersion();
            $module = $instance->providerModule();
        } catch (Throwable $exception) {
            return "{$class} could not be instantiated: {$exception->getMessage()}";
        }

        if ($module !== $dirname) {
            return "{$class} reports the provider module \"{$module}\" instead of \"{$dirname}\"";
        }

        $reason = Definitions::incompatibility(Definitions::version($capability), $version);
        $ui = array_values(array_filter(
            self::UI_CONTRACTS,
            static fn (string $interface): bool => $reflection->implementsInterface($interface),
        ));

        return [
            'capability' => $capability,
            'provider' => [
                'module' => $dirname,
                'file' => $file,
                'class' => $class,
                'version' => $version,
                'compatible' => $reason === null,
                'reason' => $reason,
                'ui' => $ui,
            ],
        ];
    }

    /**
     * Include the file in an isolated scope, with warnings turned into
     * exceptions; output of the file is discarded.
     */
    private function load(string $file, string $class): void
    {
        if (class_exists($class, false)) {
            return;
        }

        set_error_handler(static function (int $severity, string $message, string $errorFile, int $line): bool {
            throw new ErrorException($message, 0, $severity, $errorFile, $line);
        });
        ob_start();
        $level = ob_get_level();

        try {
            (static function (string $file): void {
                include_once $file;
            })($file);
        } finally {
            restore_error_handler();

            while (ob_get_level() >= $level) {
                ob_end_clean();
            }
        }
    }

    /**
     * The consumer declarations of a module (design D9), by object type.
     *
     * @param Module $module
     * @return array<string, array{default: list<string>, unsupported: list<string>}>
     */
    private function support(string $dirname, array $module): array
    {
        $declarations = [];

        foreach ($module['support'] as $item => $declaration) {
            if (! is_array($declaration)) {
                continue;
            }

            $declarations["{$dirname}/{$item}"] = [
                'default' => self::names($declaration['default'] ?? []),
                'unsupported' => self::names($declaration['unsupported'] ?? []),
            ];
        }

        return $declarations;
    }

    /** @return list<string> */
    private static function names(mixed $names): array
    {
        if (! is_array($names)) {
            return [];
        }

        return array_values(array_unique(array_map('strval', $names)));
    }
}
