## Why

ImpressCMS has no generic way to add optional features such as tags, taxonomies, comments, ratings, workflows or SEO metadata to existing IPF objects. Today a module that wants tags has to call a specific tag module directly (`icms_getModuleHandler('tag', 'smarttags')`). That module becomes a hard dependency, and a site cannot swap it for another tag module. Each feature also invents its own integration. `Icms\Ipf\Seo\Entity`, for example, hard-codes SEO fields into a base class that consumers must extend. Pre-3 is reworking IPF anyway, which makes this the right time to add one extension mechanism: modules provide "capabilities", and consumer modules depend only on capability contracts.

## What Changes

- Introduce a **Capability Registry** (`Icms\Capability\Registry`, with the legacy alias `icms_capability_Registry`). It discovers capability providers in installed modules, records which capabilities exist, resolves the one active provider for each capability and serves runtime lookups (`Registry::capability('tagging')`). It works in legacy requests and in Symfony-handled requests. In Symfony requests it is also available as a container service.
- Introduce **capability contracts** in the core library:
  - a generic base contract (`Contract\BaseInterface`)
  - a provider-neutral object reference (`ObjectRef`: module, item, id) built from an `icms_ipf_Object`
  - two versioned capability-specific contracts as the reference capabilities: `Contract\Tagging\CapabilityInterface` and `Contract\Taxonomy\CapabilityInterface`

  Consumers type against these contracts and never against a provider module.
- Introduce **provider registration by naming convention**, as for preloads and IPF module classes. A provider module places one class per capability in `capability/<Name>.php` (class `mod_<dirname>_<Name>Capability`). The class itself declares what it offers:
  - the capability, through its file name
  - the contract and contract version, through the contract it implements
  - its optional UI contributions, through the contributor contracts it implements

  No manifest or `$modversion` key is needed. The `capability/` directories are scanned when a module is installed, updated, activated, deactivated or uninstalled, and the result is compiled into a cached registry snapshot. Ordinary requests never scan the filesystem, unlike preloads.
- Introduce **provider selection**: one active provider per capability.
  - A capability with a single provider resolves to it automatically.
  - With several providers, the administrator picks one on a new system admin page ("Capabilities").
  - Uninstalling the active provider, a contract version mismatch, and switching providers are explicit, confirmed conflict states. They are never resolved silently.
- Introduce **capability assignment per IPF object type** (`<module>/<item>`, as declared in `object_items`). The administrator enables or disables each capability for each object type on the same admin page. Consumer modules may declare object types or capabilities as unsupported, but they do not have to change anything to become eligible.
- Introduce **IPF integration points** so that the active providers contribute without changes to consumer modules:
  - a form section with fields in `Icms\Ipf\Form\Base`
  - save and delete propagation from `Icms\Ipf\Handler` (`afterSave`/`afterDelete`)
  - output in the generic IPF single view
  - filters in `Icms\Ipf\View\Table`
  - an `<{icms_capability}>` Smarty tag for custom templates

  Providers ship their own admin pages and blocks as ordinary module features.
- Follow **provider ownership (Model A)**: each provider owns its tables, handlers, persistence and business logic. The framework stores no capability data. It only stores its own configuration (the active provider per capability and the assignment per object type) in two small IPF-managed tables of the system module.
- Add **reference providers** for tagging and taxonomy as test fixture modules. They prove the contracts end to end, including two competing tagging providers and a switch between them.

Nothing is BREAKING. Every integration point is additive and inert when no capability is assigned, so existing IPF modules and `Icms\Ipf\Seo\Entity` behave as before.

## Capabilities

### New Capabilities
- `capability-registry`: Provider discovery by naming convention (`capability/<Name>.php`), the compiled registry snapshot, active provider resolution and the runtime lookup API for consumers.
- `capability-contracts`: The generic capability contract, `ObjectRef`, contract versioning, and the reference `tagging` and `taxonomy` contracts that providers implement and consumers depend on.
- `capability-provider-selection`: Administration of the active provider per capability, including automatic resolution, provider switching and conflict handling (uninstall, version mismatch, missing provider).
- `capability-assignment`: Enabling capabilities per IPF object type in the administration, consumer opt-outs, and how consumers and providers check whether a capability is enabled for an object.
- `capability-ipf-integration`: How active providers contribute form fields, persistence propagation, view output, table filters, the Smarty tag, blocks and admin pages to IPF objects without changes to consumer modules.

### Modified Capabilities
<!-- None: the existing specs (host-deployment, legacy-request-bridge, request-ownership, session-identity, shared-runtime) are not affected. -->

## Impact

- **Library (`htdocs/libraries/Icms`)**:
  - new namespace `Icms\Capability` (registry, contracts, compiler, snapshot)
  - additive, inert extension points in `Icms\Ipf\Handler` (`insert()`/`delete()`), `Icms\Ipf\Form\Base`, `Icms\Ipf\View\Single` and `Icms\Ipf\View\Table`
  - legacy aliases in `aliases.php`
- **System module**:
  - a new admin function `capabilities` (provider selection and assignment matrix)
  - two IPF entities and tables: `system_capabilityprovider` and `system_capabilityassignment`
  - hooks in `modulesadmin` install, update and uninstall that rebuild the registry snapshot
  - a database upgrade step that creates the tables
- **Smarty**: a new `icms_capability` function plugin.
- **Host (`src/`)**: the registry is registered as a container service so that Symfony controllers can use it. The metadata-driven `IpfEntityType` gets the same capability form section through a form type extension.
- **Cache**: one registry snapshot file in the ICMS cache directory. It is rebuilt on module lifecycle events and from the admin page.
- **Dependencies**: none.
- **Out of scope**:
  - capability contracts for comments, ratings, workflow, SEO metadata and versioning (the design shows how they fit; each gets its own change)
  - migrating data between providers on a switch (the design defines an optional transfer contract; implementing it is future work)
  - non-IPF objects
  - Doctrine or Symfony entities
  - per-instance (per-object) capability configuration
