## Purpose

Discovers capability providers in installed modules, records which capabilities exist and which providers offer them, resolves the single active provider for each capability and gives consumer modules a provider-neutral runtime lookup.

## ADDED Requirements

### Requirement: Providers are discovered by naming convention
An installed module SHALL become a capability provider by placing one provider class per capability in its `capability/` directory. The file SHALL be named after the capability (`capability/Tagging.php` for `tagging`), and the class SHALL be named `mod_<dirname>_<Name>Capability`. No separate declaration file or module metadata key SHALL be needed. What the provider offers SHALL be derived from the class itself:
- the capability, from the file name
- the contract version, from the contract the class implements and the version it reports
- the UI contributions, from the contributor contracts the class implements

One module MAY provide several capabilities, and several modules MAY provide the same capability. A module without a `capability/` directory SHALL NOT be a provider.

#### Scenario: Module providing two capabilities
- **WHEN** a module with `capability/Tagging.php` and `capability/Taxonomy.php` is installed
- **THEN** the registry lists that module as a provider of both `tagging` and `taxonomy`

#### Scenario: Two modules providing the same capability
- **WHEN** two installed modules both contain `capability/Tagging.php`
- **THEN** the registry lists both as providers of `tagging`

#### Scenario: UI contributions detected from the class
- **WHEN** a provider class implements the tagging contract and the form contributor contract, but not the view contributor contract
- **THEN** the registry records that provider as contributing to forms and not to views

#### Scenario: Invalid provider class
- **WHEN** a file in `capability/` is named after an unknown capability, does not declare the expected class, does not implement that capability's contract, or fails to load
- **THEN** that provider is rejected and the module administration shows an error naming the module, the file and the reason, while the module's other valid providers are registered and the module itself still installs

#### Scenario: Other files in the directory
- **WHEN** the `capability/` directory also contains `index.html` or files that are not PHP
- **THEN** those files are ignored without an error

### Requirement: Discovery follows the module lifecycle
The registry SHALL be rebuilt when a module is installed, updated, activated, deactivated or uninstalled, and when an administrator requests a rebuild. Only installed, active modules SHALL count as providers. Ordinary requests SHALL NOT scan module directories or load provider classes to discover them, unlike preloads. They SHALL use the most recently built registry.

#### Scenario: Provider module uninstalled
- **WHEN** a provider module is uninstalled
- **THEN** it no longer appears as a provider of any capability on the next request

#### Scenario: Provider module deactivated
- **WHEN** a provider module is deactivated in the module administration
- **THEN** its capabilities are treated as not provided by it until it is activated again

#### Scenario: Provider file added without a module update
- **WHEN** a provider file is added to or removed from a module's `capability/` directory, but the module is not updated and no rebuild is requested
- **THEN** the registry keeps serving the previously built providers

#### Scenario: Missing registry cache
- **WHEN** the built registry is missing or unreadable at the start of a request
- **THEN** the registry is rebuilt from the installed modules before the lookup is answered

### Requirement: Known capabilities are discoverable
The registry SHALL list every known capability with its name, title, contract version, providers and active provider. Each capability SHALL also carry a resolution state:
- `active`: an active provider is selected
- `unresolved`: providers exist, but none is active
- `unavailable`: no provider is installed

#### Scenario: Listing capabilities
- **WHEN** `tagging` has two providers with one selected, and `taxonomy` has none
- **THEN** the listing shows `tagging` as `active` with both providers, and `taxonomy` as `unavailable`

### Requirement: Consumers look up capabilities by name only
Consumer modules SHALL obtain a capability by its name, without naming a provider module. They receive the active provider's implementation of the capability contract. The same lookup SHALL work in legacy requests and in framework-handled requests and SHALL return the same instance within one request. A consumer SHALL be able to ask whether a capability is available without triggering an error.

#### Scenario: Active capability lookup
- **WHEN** a consumer requests `tagging` and AdvancedTags is the active tagging provider
- **THEN** it receives AdvancedTags' implementation of the tagging contract

#### Scenario: Unavailable capability
- **WHEN** a consumer requests `workflow` and that capability is `unavailable` or `unresolved`
- **THEN** the availability check returns false, and a direct request fails with a dedicated "capability unavailable" error that names the capability and its state, not with a fatal PHP error

#### Scenario: Same instance in a request
- **WHEN** two consumers request `tagging` during the same request
- **THEN** both receive the same service instance

### Requirement: Contract versions are checked at resolution
The registry SHALL activate a provider only if its declared contract version is compatible with the contract version in the core: same major version, and a minor version equal to or greater than the core's. Incompatible declarations SHALL be listed with the reason they are not usable, and SHALL NOT be resolved.

#### Scenario: Provider built for an older major version
- **WHEN** the core's tagging contract is 2.0 and a provider declares 1.3
- **THEN** that provider is listed as incompatible and cannot be selected as active

### Requirement: Registry lookups are cheap
Resolving the active provider of a capability SHALL NOT perform database queries or filesystem scans beyond loading the built registry and the capability configuration once per request. A request in which no capability is used SHALL NOT instantiate any provider service.

#### Scenario: Request without capability use
- **WHEN** a page that uses no capability is rendered
- **THEN** no provider service is constructed and no provider file is loaded
