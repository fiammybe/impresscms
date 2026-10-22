## 1. Core types and contracts (D2, D3, D4, D12, D13)

- [x] 1.1 Create `htdocs/libraries/Icms/Capability/` with `ObjectRef`, the `State` enum (`Active`, `Unresolved`, `Unavailable`), `Exception\UnavailableException` and `Exception\UnsupportedObjectException`. Register the legacy aliases (`icms_capability_Registry`, `icms_capability_ObjectRef`) in `aliases.php`
- [x] 1.2 Add `Contract\BaseInterface` with `capabilityName()`, `contractVersion()`, `providerModule()`, `supportsObjectType()`, `clear()` and `adminUrl()`
- [x] 1.3 Add `Contract\Tagging\CapabilityInterface` (version 1.0) and the `Tag` value object, as in D12
- [x] 1.4 Add `Contract\Taxonomy\CapabilityInterface` (version 1.0) and the `Vocabulary` and `Term` value objects, as in D13
- [x] 1.5 Add `Definitions`, the catalogue of known capabilities: name → contract interface, version, title language constant. Start with `tagging` and `taxonomy`
- [x] 1.6 Add the contributor interfaces in `Ui\`: Form, Persistence, View, TableFilter, TemplateRenderer (D10). Add the `Submission` value object
- [x] 1.7 Add `ObjectRef::resolve()` and `ObjectRef::resolveMany()` on top of `Icms\Ipf\Registry\Handler` (D4): one `addObjectsFromHandler()` call per object type with the missing and the already cached ids, `getSingleObject()` for the lookup, and a skip for modules that are not installed and active
- [x] 1.8 Unit tests: `ObjectRef::fromEntity()` for a stored IPF object, an unsaved one and a non-IPF object; equality and `toString()`; the version compatibility rule (same major, provider minor ≥ core minor) (spec `capability-contracts`). Integration tests for `resolveMany()`: one query per object type for 20 refs, no second query for refs already loaded in the request, nothing for an uninstalled module or a deleted object

## 2. Convention-based discovery and registry snapshot (D5, D6)

- [x] 2.1 Implement `Compiler`, which scans `modules/<dirname>/capability/*.php` of the installed, active modules. It maps the file name to the capability (`Tagging.php` → `tagging`) and the class name `mod_<dirname>_<Name>Capability`, and ignores `index.html` and files that are not PHP
- [x] 2.2 Load each provider file in an isolated scope, inside an error handler that turns warnings, errors and `Throwable`s into a provider error. Check that the capability is known in `Definitions`, that the expected class exists, has an argument-free constructor and implements the capability's contract, and that `contractVersion()` is compatible. Record the implemented `Ui\*` contracts. Collect errors per module and file without dropping the module's valid providers
- [x] 2.3 Write the snapshot (module, file path, class, version, compatibility, `Ui\*` contracts, errors) atomically to `ICMS_CACHE_PATH/capability-registry.php` (temporary file + rename). Implement a lazy rebuild when the file is missing or unreadable, and a debug-mode mtime check on the `capability/` directories
- [x] 2.4 Unit tests with fixture modules: one module with two provider files, two modules with the same capability, an unknown capability file name, a missing class, a class that does not implement the contract, a file with a fatal error, an incompatible version, a provider that implements only the form contributor, a deactivated module (spec `capability-registry`)

## 3. System configuration entities (D7)

- [x] 3.1 Add the IPF entities and handlers `capabilityprovider` and `capabilityassignment` to the system module, with tables `system_capabilityprovider` and `system_capabilityassignment`
- [x] 3.2 Add the table creation to the system module install, to its update, and to the `upgrade/` path for existing sites. Add `capabilityprovider` and `capabilityassignment` to the system module's `object_items`
- [x] 3.3 Integration test: a fresh install and an upgrade both create the empty tables

## 4. Registry and runtime lookup (D1, D6, D8)

- [x] 4.1 Implement `Registry` as a static facade over a per-request instance: `capability()`, `find()`, `has()`, `state()`, `capabilities()`, `providers()`, `rebuild()`
- [x] 4.2 Load the snapshot and `system_capabilityprovider` at most once per request, and only on first use. Load the table through `Icms\Ipf\Registry\Handler::addObjectsFromHandler()` (D7), not through a cache of its own. Instantiate provider classes lazily, once per request, by `require_once` of the file path in the snapshot. Use no directory scan and no autoloader
- [x] 4.3 Derive the state per capability. Auto-activate when there is no selection and exactly one compatible provider. Keep a stale selection as `Unresolved`, and never fall back to another provider
- [x] 4.4 Fire `Icms\Event` notifications `icms_capability`/`providerActivated`, `providerSwitched` and `capabilityUnavailable`
- [x] 4.5 Integration tests: lookup of the active provider; the same instance twice per request; the `unavailable` error naming the capability and its state; a page without capability use loads no provider file (spec `capability-registry`)

## 5. Assignment map (D9)

- [x] 5.1 Implement `AssignmentMap`. It loads `system_capabilityassignment` once per request through `Icms\Ipf\Registry\Handler::addObjectsFromHandler()` (D7) and evaluates `isEnabled(objectType, capability)` with all four conditions of D9
- [x] 5.2 Read consumer declarations (`$modversion['capability_support']`: `default`, `unsupported`) into the snapshot during compilation. Apply defaults when a capability first becomes active (`source = default`)
- [x] 5.3 Add `Registry::isEnabledFor(object|string $objectOrType, string $capability)`
- [x] 5.4 Remove the assignments of a consumer module's object types when that module is uninstalled. Keep them when a provider disappears or is switched
- [x] 5.5 Tests for every scenario of spec `capability-assignment`

## 6. Module lifecycle wiring (D6, D8)

- [x] 6.1 Call `Registry::rebuild()` from `modulesadmin.php` after install, update, uninstall, activate and deactivate
- [x] 6.2 Show provider errors in the module administration result messages, naming the module, the file and the reason
- [x] 6.3 Extend the uninstall confirmation with the capabilities that the module actively provides and the object types they are assigned to
- [x] 6.4 Integration tests: installing the first provider auto-activates it; installing a second one keeps the active provider; uninstalling the active one gives `Unresolved` or `Unavailable` (spec `capability-provider-selection`)

## 7. Capabilities admin page (D8, D9)

- [x] 7.1 Add the system admin function `capabilities` (`admin/capabilities/`, `icms_version.php` category, menu entry, language files), protected like the other system admin functions
- [x] 7.2 "Providers" tab: each capability with its title, version and state; its providers with version, compatibility and a link to the provider admin; selecting the active provider; a "Rebuild registry" action
- [x] 7.3 Switch confirmation: the screen lists the affected object types and states that data stays with the previous provider. Saving requires a valid security token
- [x] 7.4 "Assignments" tab: a matrix of object types × capabilities. Unsupported and unavailable cells are disabled. Saving requires a valid security token
- [x] 7.5 Show a warning banner on the system admin dashboard for each capability in a conflict state
- [x] 7.6 After saving a selection or the matrix, refresh the cached sets in `Icms\Ipf\Registry\Handler` before anything else reads them in the same request
- [x] 7.7 HTTP tests: access denied for non-admins; switching with and without a token; switching back shows the old data; matrix saves; an unsupported cell is rejected

## 8. IPF integration points (D10, D11)

- [x] 8.1 Implement `Integration` with `contributeToForm()`, `afterStored()`, `afterDeleted()`, `viewRows()`, `tableFilters()`/`tableCriteria()`, `prefetch()` and `withSubmission()`, plus the request-level set of processed refs
- [x] 8.2 `Icms\Ipf\Form\Base::createElements()`: add one capability section per enabled capability, with field prefix `capability[<name>]`, and a hidden `capability_token`. Add nothing when no capability is enabled
- [x] 8.3 `Icms\Ipf\Handler::insert()`: call `afterStored()` after the after-events have succeeded, process only with the capability token and submitted fields, and report provider errors through `setErrors()`
- [x] 8.4 `Icms\Ipf\Handler::delete()`: call `afterDeleted()` after `afterDelete` has succeeded, and log provider failures without undoing the delete
- [x] 8.5 `Icms\Ipf\View\Single`: add the rows from view contributors after the object's rows
- [x] 8.6 `Icms\Ipf\View\Table`: add the filter selects of filter contributors, restrict by key `IN` (or no match for an empty set) combined with the existing criteria, and prefetch view data for the page's refs
- [x] 8.7 Add the Smarty plugin `function.icms_capability.php`. It accepts `object`, or `module`/`item`/`id`, plus `name`, `variant` and extra params, and renders `''` when the capability is disabled or unavailable
- [x] 8.8 Characterization tests: the form HTML, the stored rows, the single view and the table output of an IPF type without enabled capabilities are byte-identical before and after (spec `capability-ipf-integration`, "Integration is inert")
- [x] 8.9 Tests: a field name collision with a consumer variable named `tags`; a programmatic save leaves provider data untouched; a failed object save calls no provider; double processing within one request is prevented

## 9. Reference providers as test fixtures (D12, D13)

- [x] 9.1 Add the fixture module `smarttags` (tables `smarttags_tags` and `smarttags_links`) with `capability/Tagging.php` delegating to its own IPF handlers and implementing the Form, Persistence, View, TableFilter and Template contributors
- [x] 9.2 Add the fixture module `advancedtags`, a second tagging provider with its own table layout, an admin page, a suggest endpoint and a tag cloud block with an object type option
- [x] 9.3 Add the fixture module `taxonomy` (tables `taxonomy_vocabulary`, `taxonomy_term` and `taxonomy_relation`) implementing taxonomy, with a hierarchical term select, breadcrumbs, a term filter and a browser block
- [x] 9.4 Add the fixture module `knowledgemanager`, which provides both `tagging` and `taxonomy` through `capability/Tagging.php` and `capability/Taxonomy.php`
- [x] 9.5 Add the fixture consumer module `news` with the items `article` and `category`, a `capability_support` declaration, and no capability code
- [x] 9.6 Add batch reads in every reference provider, resolve consumer objects only through `ObjectRef::resolveMany()`, and a test that a table page of 20 articles costs a fixed number of provider queries
- [x] 9.7 End-to-end tests: tag an article through the legacy form; filter the admin table by tag; the single view shows tags; the Smarty tag renders in a template override; deleting the article clears its tags; switching tagging from SmartTags to AdvancedTags and back
- [x] 9.8 Add `clearType(objectType)` to the base contract and implement it in the reference providers. Call it when a consumer module is uninstalled

## 10. Symfony host bridge (D14)

- [x] 10.1 Register `Icms\Capability\Registry` as a service in `config/services/icms.yaml`. Add lazy factory services for `Contract\Tagging\CapabilityInterface` and `Contract\Taxonomy\CapabilityInterface`
- [x] 10.2 Implement `App\Ipf\Form\CapabilityFormExtension` for `IpfEntityType`, with the same field names, and adapt text, select and hidden elements from the form contributors
- [x] 10.3 Pass the bound capability data to `Integration::withSubmission()` before `IpfPersister::save()`
- [x] 10.4 Functional test: a Symfony-handled IPF form for a fixture type with `tagging` enabled shows the section and stores tags exactly like the legacy page

## 11. Documentation and quality gates

- [x] 11.1 Write `docs/capabilities.md`: concepts, the provider naming convention (`capability/<Name>.php`, `mod_<dirname>_<Name>Capability`, constructor rules), the contracts, the contributor interfaces, the consumer declarations, the Smarty tag, theme overrides, and the switch semantics (data stays with its provider)
- [x] 11.2 Add a "Writing a capability provider" walkthrough based on the `advancedtags` fixture
- [x] 11.3 Run PHPStan on `htdocs/libraries/Icms/Capability` and `src/` at the project level, and run the full PHPUnit suite. Tag the tests with `#[Group('spec:<capability>')]` and their `@scenario`
