# Capabilities

A *capability* is an optional feature for IPF objects, such as tagging or a
taxonomy, that a module provides and other modules use without depending on
that module. A news module gets tags on its articles without naming a tag
module. A site can replace one tag module by another without changing the news
module.

- **Providers** are modules that implement a capability: they own its tables,
  handlers and business logic, and may add form fields, view output, table
  filters, template output, admin pages and blocks.
- **Consumers** are modules with IPF objects (`icms_ipf_Object`). They need no
  code to get capability fields in their IPF forms, saving and deleting, the
  generic single view and the filters of their admin tables. They depend only on
  the *contracts* in the core when they use a capability in code.
- **The administrator** chooses the active provider of each capability, and
  which capabilities are enabled for which object type, on the system admin page
  *Capabilities* (`modules/system/admin.php?fct=capabilities`).

The design is in `openspec/changes/ipf-capability-provider-framework/`.

## Concepts

| Term | Meaning |
|---|---|
| capability | a name the core knows, with a contract: `tagging`, `taxonomy` |
| contract | an interface in `Icms\Capability\Contract\<Name>\CapabilityInterface`, extending `Contract\BaseInterface`, with a version (`VERSION`, "1.0") |
| provider | a class in a module's `capability/` directory that implements a contract |
| active provider | the one provider of a capability that is used; at most one per capability |
| object type | an IPF item of a module, `<module>/<item>`, such as `news/article` (from `$modversion['object_items']`) |
| assignment | whether a capability is enabled for an object type |
| object reference | `Icms\Capability\ObjectRef`: module, item and id of an IPF object |

A capability has one of three states:

- `active`: a usable provider is chosen;
- `unresolved`: providers exist, but none is chosen, or the chosen one is gone;
- `unavailable`: no compatible provider is installed.

## Using a capability in a consumer

Nothing is needed for forms, saving, deleting, the single view and admin table
filters. To use a capability in code, ask the registry by name:

```php
use Icms\Capability\Contract\Tagging\CapabilityInterface as Tagging;
use Icms\Capability\ObjectRef;
use Icms\Capability\Registry;

if (! Registry::isEnabledFor($article, Tagging::NAME)) {
    return [];
}

/** @var Tagging $tagging */
$tagging = Registry::capability(Tagging::NAME);

$tags = $tagging->getTags(ObjectRef::fromEntity($article));
```

- `Registry::capability($name)` returns the active provider's implementation,
  the same instance for the whole request, or throws
  `Icms\Capability\Exception\UnavailableException` (with the state).
- `Registry::find($name)` returns it or `null`; `Registry::has($name)` and
  `Registry::state($name)` check it.
- `Registry::isEnabledFor($objectOrType, $name)` is true when the capability is
  assigned to the object type, not unsupported by the consumer, active, and
  served by the active provider for that type.
- In Symfony controllers, inject `Icms\Capability\Registry` or a contract
  (`Icms\Capability\Contract\Tagging\CapabilityInterface`); both are services.

Read operations for lists take several references at once
(`getTagsForObjects()`, `termsOfObjects()`): use them for a page of objects.
`ObjectRef::resolveMany($refs)` loads the IPF objects behind references with one
query per object type, through the per-request IPF object cache.

### Consumer declarations

A consumer may declare, next to its `object_items`, which capabilities are
enabled by default for an item (when the capability first becomes active) and
which it does not support:

```php
$modversion['object_items'] = [1 => 'article', 'category'];

$modversion['capability_support'] = [
    'article' => ['default' => ['tagging']],
    'category' => ['unsupported' => ['tagging']],
];
```

Both are optional. An unsupported capability cannot be enabled for that type.

### Templates and themes

Custom templates show capability output with the `icms_capability` tag. It
renders nothing when the capability is not enabled for the object's type:

```smarty
<{icms_capability name="tagging" object=$article variant="list"}>
<{icms_capability name="tagging" module="news" item="article" id=$article.article_id variant="cloud"}>
```

The variants are the provider's. A theme can add the tag to a module without
changing the module, with a template override in
`themes/<theme>/modules/<module>/<template>.html`.

## The administrator's view

The *Providers* tab lists every capability with its contract version, state
and providers (module, version, contract version, compatibility, a link to the
provider's own administration). The administrator chooses the active provider
there.

- The only compatible provider of a capability becomes active when it is
  installed. A provider installed later does not take over.
- A provider is compatible when its contract version has the core's major
  version and at least its minor version.
- Switching the provider of a capability that is enabled for object types is
  confirmed first. **The data stays with the provider that stored it**: tags
  stored by one module are not visible through another. Switching back shows
  them again, because nothing is deleted.
- When the active provider is uninstalled, deactivated or no longer
  compatible, nothing switches automatically: the capability is `unresolved`
  (or `unavailable` without providers) and the admin dashboard shows a warning
  until a provider is chosen. The uninstall confirmation of a module names the
  capabilities and object types it provides.

The *Assignments* tab is a matrix of object types and capabilities.
Assignments are kept when a provider goes away or is switched, and removed when
the consumer module is uninstalled; then the active providers also remove their
data for that module's object types.

Changes on the page need the ImpressCMS security token. *Rebuild registry*
scans the modules again (see below).

## Writing a capability provider

A provider is found by **naming convention**, as preloads and IPF module
classes are. Take the tagging provider of the AdvancedTags test module
(`tests/Fixtures/capability-site/modules/advancedtags/`) as the example.

### 1. The provider class

One file per capability in the module's `capability/` directory. The file is
named after the capability, the class is `mod_<dirname>_<Name>Capability`:

```
modules/advancedtags/capability/Tagging.php   →   class mod_advancedtags_TaggingCapability
```

The class implements the capability's contract:

```php
use Icms\Capability\Contract\Tagging\CapabilityInterface;
use Icms\Capability\ObjectRef;

class mod_advancedtags_TaggingCapability implements CapabilityInterface
{
    public function capabilityName(): string { return self::NAME; }
    public function contractVersion(): string { return '1.0'; }
    public function providerModule(): string { return 'advancedtags'; }
    public function supportsObjectType(string $objectType): bool { return true; }
    public function adminUrl(): ?string { return ICMS_URL . '/modules/advancedtags/admin/tags.php'; }

    public function setTags(ObjectRef $ref, array $labels): void
    {
        $this->links()->replaceFor($ref, $this->tags()->idsFor($labels));
    }

    // getTags, getTagsForObjects, findObjects, suggest, cloud, clear, clearType, …

    private function links(): mod_advancedtags_LinkHandler
    {
        return icms_getModuleHandler('link', 'advancedtags');
    }
}
```

Rules:

- **The constructor takes no arguments and has no side effects.** The registry
  creates the class when it is first used, and the module administration
  creates it once to read its contract version. Fetch handlers inside the
  methods.
- **The module owns the data.** Store object references (module, item, id) in
  the module's own IPF tables; never add columns to, or join, the consumer's
  tables. `clear()` and `clearType()` remove what the module stores for an
  object or an object type.
- **Read lists in batches.** `getTagsForObjects()` must cost a fixed number of
  queries, whatever the number of objects.
- **`providerModule()` returns the module's dirname**; a class that reports
  another module is rejected.
- **Escape HTML** in everything the provider renders.

### 2. Optional UI contributions

The class contributes what it implements, besides the contract:

| Interface (`Icms\Capability\Ui\`) | Contribution |
|---|---|
| `FormContributorInterface` | `formElements()`: the fields of its section in IPF edit forms, named under the given prefix (`capability[tagging][tags]`) |
| `PersistenceContributorInterface` | `store()`: stores those fields after the object was saved; returns error messages |
| `ViewContributorInterface` | `prefetch()` and `viewRow()`: a row in the generic IPF single view |
| `TableFilterContributorInterface` | `filterOptions()` and `matchingIds()`: a filter in IPF admin tables |
| `TemplateRendererInterface` | `render()`: the output of the `icms_capability` template tag, per variant |

The form fields are only stored for a form that carries the capability's own
security token (which the framework adds), so imports and other programmatic
saves never change provider data. Validate leniently: the object is already
stored when `store()` runs.

### 3. Admin pages, endpoints and blocks

These are ordinary module features: admin pages (`adminindex`, `adminmenu`),
blocks (`$modversion['blocks']`) and user-side pages. Read data through the
contract (`Registry::find('tagging')`), never through the consumer. Endpoints
check the module's own access rights. `adminUrl()` links the provider's
administration from the capabilities page.

### 4. Installing

Discovery runs when a module is installed, updated, uninstalled, activated or
deactivated, and when *Rebuild registry* is used. It writes the compiled
registry to `cache/capability-registry.php`; ordinary requests read that file and
never scan module directories. A provider file that cannot be used (unknown
capability, missing class, wrong contract, failing to load) is reported in the
module administration's result, and the module's other providers still work.

During development, with debug mode on, a provider file that is newer than the
compiled registry triggers a rebuild.

Errors that PHP raises while compiling a class (for example a class that does
not implement every method of its interface) end the request that discovers
it. Lint the provider class before installing it.

## Adding a capability to the core

A new capability is a core change: a contract in
`Icms\Capability\Contract\<Name>\`, an entry in `Icms\Capability\Definitions`
and its language constant, and at least one provider. Within a major contract
version, interfaces do not change; additional operations go into new
sub-interfaces until the next major version.
