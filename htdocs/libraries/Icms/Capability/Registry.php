<?php

/**
 * The capability registry
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		ICMS\Capability
 */

declare(strict_types=1);

namespace Icms\Capability;

use Icms\Capability\Config\Assignment;
use Icms\Capability\Config\AssignmentHandler;
use Icms\Capability\Config\ProviderHandler;
use Icms\Capability\Contract\BaseInterface;
use Icms\Capability\Exception\UnavailableException;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Discovery, provider selection, assignment and runtime lookup of
 * capabilities (design D1, D6, D8).
 *
 * Consumers name a capability, never a provider module:
 *
 *     $tagging = Registry::capability('tagging');
 *
 * The static methods are a facade over one instance per request. The instance
 * reads the compiled snapshot and the selected providers at most once, and
 * creates a provider only when it is first used.
 */
final class Registry
{
    private static ?self $instance = null;

    private ?RegistrySnapshot $snapshot = null;

    private ?AssignmentMap $assignments = null;

    /** @var array<string, BaseInterface> provider instances by capability */
    private array $services = [];

    public function __construct(
        private readonly string $cacheFile,
        private readonly Compiler $compiler,
    ) {
    }

    public static function instance(): self
    {
        return self::$instance ??= new self(ICMS_CACHE_PATH . '/capability-registry.php', Compiler::forSite());
    }

    /** Use another instance, or start over with a fresh one on the next call (tests). */
    public static function setInstance(?self $registry): void
    {
        self::$instance = $registry;
        ObjectRef::reset();
        Integration::reset();
    }

    /**
     * The active provider's implementation of the capability's contract.
     *
     * @throws UnavailableException when the capability has no active provider
     */
    public static function capability(string $name): BaseInterface
    {
        $registry = self::instance();
        $service = $registry->service($name);

        if ($service === null) {
            throw new UnavailableException($name, $registry->stateOf($name));
        }

        return $service;
    }

    /** The active provider's implementation, or null when the capability has none. */
    public static function find(string $name): ?BaseInterface
    {
        return self::instance()->service($name);
    }

    /** Whether the capability has an active provider. */
    public static function has(string $name): bool
    {
        return self::instance()->stateOf($name) === State::Active;
    }

    public static function state(string $name): State
    {
        return self::instance()->stateOf($name);
    }

    /**
     * Whether the capability is enabled for an IPF object or an object type
     * ("news/article"): assigned, not unsupported by the consumer, active, and
     * served by the active provider (design D9).
     */
    public static function isEnabledFor(object|string $objectOrType, string $name): bool
    {
        $objectType = is_string($objectOrType) ? $objectOrType : ObjectRef::typeOf($objectOrType);

        return self::instance()->assignments()->isEnabled($objectType, $name);
    }

    /** @return list<string> every capability the core knows */
    public static function capabilities(): array
    {
        return Definitions::names();
    }

    /**
     * Scan the installed modules again, write the snapshot, and auto-activate
     * capabilities that have exactly one compatible provider and no selection.
     */
    public static function rebuild(): RegistrySnapshot
    {
        return self::instance()->rebuildSnapshot();
    }

    public function snapshot(): RegistrySnapshot
    {
        if ($this->snapshot !== null) {
            return $this->snapshot;
        }

        $snapshot = $this->readSnapshot();

        if ($snapshot === null || $this->isStale($snapshot)) {
            return $this->rebuildSnapshot();
        }

        return $this->snapshot = $snapshot;
    }

    /**
     * Read the snapshot, the selections and the assignments again, after
     * another request or process changed them.
     */
    public function reload(): void
    {
        $this->snapshot = null;
        $this->assignments = null;
        $this->services = [];
        $this->providerHandler()->selections(true);
        $this->assignmentHandler()->matrix(true);
    }

    public function rebuildSnapshot(): RegistrySnapshot
    {
        $snapshot = $this->compiler->compile(Compiler::installedModules());
        $this->writeSnapshot($snapshot);
        $this->snapshot = $snapshot;
        $this->services = [];
        $this->reconcile();

        return $snapshot;
    }

    /** The module selected as the capability's provider, also when it is gone. */
    public function selectionOf(string $name): ?string
    {
        $selection = $this->providerHandler()->selections()[$name] ?? null;

        return $selection?->provider();
    }

    public function stateOf(string $name): State
    {
        if (! Definitions::has($name)) {
            return State::Unavailable;
        }

        $compatible = $this->snapshot()->compatibleProviders($name);

        if ($compatible === []) {
            return State::Unavailable;
        }

        $selected = $this->selectionOf($name);

        if ($selected !== null && in_array($selected, $compatible, true)) {
            return State::Active;
        }

        return State::Unresolved;
    }

    /** The dirname of the active provider, or null. */
    public function activeProviderOf(string $name): ?string
    {
        return $this->stateOf($name) === State::Active ? $this->selectionOf($name) : null;
    }

    public function service(string $name): ?BaseInterface
    {
        if (isset($this->services[$name])) {
            return $this->services[$name];
        }

        $module = $this->activeProviderOf($name);

        if ($module === null) {
            return null;
        }

        $service = $this->providerInstance($name, $module);

        if ($service === null) {
            return null;
        }

        return $this->services[$name] = $service;
    }

    /** A new instance of one provider of the capability, active or not (the capabilities page). */
    public function providerInstance(string $name, string $module): ?BaseInterface
    {
        $provider = $this->snapshot()->provider($name, $module);

        if ($provider === null) {
            return null;
        }

        if (! class_exists($provider['class'], false)) {
            require_once $provider['file'];
        }

        $service = new $provider['class']();

        if (! $service instanceof BaseInterface) {
            throw new RuntimeException("{$provider['class']} is not a capability provider.");
        }

        return $service;
    }

    /**
     * The capabilities that need the administrator (design D8): the selected
     * provider is no longer usable, or several providers exist and none is
     * selected.
     *
     * @return array<string, State> by capability
     */
    public function conflicts(): array
    {
        $conflicts = [];

        foreach (Definitions::names() as $name) {
            $state = $this->stateOf($name);

            if ($state === State::Active) {
                continue;
            }

            $selected = $this->selectionOf($name);
            $choice = $state === State::Unresolved && count($this->snapshot()->compatibleProviders($name)) > 1;

            if ($selected !== null || $choice) {
                $conflicts[$name] = $state;
            }
        }

        return $conflicts;
    }

    /**
     * The active provider of the capability as a contributor of one kind
     * (one of Compiler::UI_CONTRACTS), or null when it does not contribute it.
     *
     * @template T of object
     * @param class-string<T> $contract
     * @return T|null
     */
    public function contributor(string $name, string $contract): ?object
    {
        $module = $this->activeProviderOf($name);

        if ($module === null) {
            return null;
        }

        $provider = $this->snapshot()->provider($name, $module);

        if ($provider === null || ! in_array($contract, $provider['ui'], true)) {
            return null;
        }

        $service = $this->service($name);

        return $service instanceof $contract ? $service : null;
    }

    public function assignments(): AssignmentMap
    {
        return $this->assignments ??= new AssignmentMap($this, $this->assignmentHandler());
    }

    public function isModuleActive(string $dirname): bool
    {
        return in_array($dirname, $this->snapshot()->modules(), true);
    }

    /**
     * Make the module the active provider of the capability (the capabilities
     * page, after confirmation).
     */
    public function selectProvider(string $name, string $module, int $uid): void
    {
        if (! in_array($module, $this->snapshot()->compatibleProviders($name), true)) {
            throw new InvalidArgumentException("{$module} is not a compatible provider of \"{$name}\".");
        }

        $previous = $this->selectionOf($name);
        $wasActive = $this->stateOf($name) === State::Active;

        if (! $this->providerHandler()->select($name, $module, $uid)) {
            throw new RuntimeException("The provider of \"{$name}\" could not be stored.");
        }

        unset($this->services[$name]);

        if ($previous === null) {
            $this->applyDefaults($name);
        }

        if ($wasActive && $previous !== $module) {
            $this->notify('providerSwitched', ['capability' => $name, 'provider' => $module, 'previous' => $previous]);

            return;
        }

        $this->notify('providerActivated', ['capability' => $name, 'provider' => $module]);
    }

    /**
     * A consumer module is being uninstalled: its object types lose their
     * assignments, and the active providers drop their data for them.
     */
    public function forgetConsumer(string $dirname): void
    {
        foreach ($this->snapshot()->objectTypes() as $objectType) {
            if (! str_starts_with($objectType, "{$dirname}/")) {
                continue;
            }

            foreach (Definitions::names() as $name) {
                $service = $this->service($name);

                if ($service === null) {
                    continue;
                }

                try {
                    $service->clearType($objectType);
                } catch (Throwable $exception) {
                    self::log("{$service->providerModule()} could not clear its {$name} data of {$objectType}: {$exception->getMessage()}");
                }
            }
        }

        $this->assignmentHandler()->deleteForModule($dirname);
        $this->assignments = null;
    }

    /**
     * The capabilities a module is the active provider of, with the object
     * types they are enabled for (the uninstall warning).
     *
     * @return array<string, list<string>> capability => object types
     */
    public function activeAssignmentsOf(string $dirname): array
    {
        $result = [];

        foreach (Definitions::names() as $name) {
            if ($this->activeProviderOf($name) !== $dirname) {
                continue;
            }

            $result[$name] = $this->assignments()->assignedTypes($name);
        }

        return $result;
    }

    public function providerHandler(): ProviderHandler
    {
        return icms_getModuleHandler('capabilityprovider', 'system');
    }

    public function assignmentHandler(): AssignmentHandler
    {
        return icms_getModuleHandler('capabilityassignment', 'system');
    }

    public static function log(string $message): void
    {
        if (class_exists('icms', false) && \icms::$logger instanceof \Icms\Core\Logger) {
            \icms::$logger->addExtra('Capabilities', htmlspecialchars($message, ENT_QUOTES));
        }

        error_log("ImpressCMS capabilities: {$message}");
    }

    /**
     * After a rebuild: auto-activate unambiguous capabilities and announce
     * capabilities whose selected provider is no longer usable (design D8).
     */
    private function reconcile(): void
    {
        foreach (Definitions::names() as $name) {
            $compatible = $this->snapshot()->compatibleProviders($name);
            $selected = $this->selectionOf($name);

            if ($selected === null && count($compatible) === 1) {
                $this->providerHandler()->select($name, $compatible[0], 0);
                $this->applyDefaults($name);
                $this->notify('providerActivated', ['capability' => $name, 'provider' => $compatible[0]]);

                continue;
            }

            if ($selected !== null && ! in_array($selected, $compatible, true)) {
                $this->notify('capabilityUnavailable', [
                    'capability' => $name,
                    'provider' => $selected,
                    'state' => $this->stateOf($name)->value,
                ]);
            }
        }
    }

    /** Enable a capability that became active for the object types whose consumer declares it a default. */
    private function applyDefaults(string $name): void
    {
        $handler = $this->assignmentHandler();

        foreach ($this->snapshot()->objectTypes() as $objectType) {
            if (! in_array($name, $this->snapshot()->defaultsFor($objectType), true)) {
                continue;
            }

            if ($this->snapshot()->isUnsupported($objectType, $name)) {
                continue;
            }

            if ($handler->cell($objectType, $name) !== null) {
                continue;
            }

            $handler->setCell($objectType, $name, true, Assignment::SOURCE_DEFAULT);
        }

        $handler->matrix(true);
        $this->assignments = null;
    }

    /** @param array<string, mixed> $parameters */
    private function notify(string $event, array $parameters): void
    {
        if (class_exists(\Icms\Event::class)) {
            \Icms\Event::trigger('icms_capability', $event, $this, $parameters);
        }
    }

    private function readSnapshot(): ?RegistrySnapshot
    {
        if (! is_file($this->cacheFile)) {
            return null;
        }

        try {
            $data = include $this->cacheFile;
        } catch (Throwable) {
            return null;
        }

        if (! is_array($data) || ! isset($data['built'], $data['providers'], $data['modules'])) {
            return null;
        }

        return new RegistrySnapshot($data);
    }

    private function writeSnapshot(RegistrySnapshot $snapshot): void
    {
        $directory = dirname($this->cacheFile);

        if (! is_dir($directory) && ! @mkdir($directory, 0775, true) && ! is_dir($directory)) {
            self::log("Cannot create {$directory} for the capability registry.");

            return;
        }

        $temporary = $this->cacheFile . '.' . bin2hex(random_bytes(6)) . '.tmp';
        $code = "<?php\n\n// Generated by Icms\\Capability\\Compiler. Do not edit: rebuilt on module install, update and uninstall.\n\nreturn "
            . var_export($snapshot->toArray(), true) . ";\n";

        if (@file_put_contents($temporary, $code, LOCK_EX) === false || ! @rename($temporary, $this->cacheFile)) {
            @unlink($temporary);
            self::log("Cannot write the capability registry to {$this->cacheFile}.");

            return;
        }

        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($this->cacheFile, true);
        }
    }

    /** In debug mode only: a provider file was added or changed after the snapshot was built. */
    private function isStale(RegistrySnapshot $snapshot): bool
    {
        $debug = (int) ($GLOBALS['icmsConfig']['debug_mode'] ?? 0);

        if ($debug <= 0) {
            return false;
        }

        return $this->compiler->sourcesModifiedAt($snapshot->modules()) > $snapshot->built();
    }
}
