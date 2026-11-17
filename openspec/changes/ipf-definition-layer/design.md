## Context

See `proposal.md` (Why). This change builds on `ipf-on-symfony-mimetype-pilot`, which delivered metadata-driven forms (`App\Ipf\Form\*`), `App\Ipf\IpfPersister`, the admin page integration (`App\Admin\*`), `App\Legacy\ModuleHandlerLocator` and switchable takeovers.

What IPF does automatically today, and where (`Icms\Ipf\…`):

| Class | Responsibility | Derived from the definition |
|---|---|---|
| `Entity` (+ `Core\Entity`) | fields in `$vars`, `getVar` formats, controls, links | captions by `_CO_<MOD>_<ITEM>_<KEY>`, `cleanVars()` validation, `toArray()`, item/edit/delete links |
| `Handler` | CRUD, criteria, events, permissions, uploads | table name, key/identifier/summary, `getList()`, permission criteria |
| `Form\Base` + `Elements\*` | legacy form | field → control by data type, sections, permission fields, JS validation |
| `Controller` | `$_POST` → object, store, delete with confirmation | `storeFromDefaultForm`, `handleObjectDeletion` |
| `View\Table`/`Column`/`Row` | list with sorting, filters, quick search, paging, actions | column titles, sortable fields, cookies for the last sort/limit |
| `Permission\Handler` | group rights per object | `saveItem_Permissions`, `getGrantedItems` |

Mapping IPF → Symfony:

| IPF concept | Symfony mechanism | Status |
|---|---|---|
| `Entity::$vars` + controls | `EntityDefinition`/`FieldDefinition` (own metadata) | this change |
| `Form\Base`, `cleanVars()` | Form + Validator (`IpfEntityType`, `ControlMap`) | exists |
| `Controller::storeFromDefaultForm` | `IpfFormFactory` + `IpfPersister` | exists |
| `Table` request parsing | `ListQuery` + `ListQueryResolver` on HttpFoundation `Request` | this change |
| `Table` criteria | `HandlerStore` → `Icms\Db\Criteria\Compo` → `handler->getObjects()/getCount()` | this change |
| `Table` rendering | `TableView` + `TableRendererInterface` (legacy and Twig) | this change |
| `toArray()` | Serializer normalizer | this change |
| ad-hoc AJAX (`internallink.php`) | `IpfApiController` (Routing) on `IpfRepository` | this change (read) |
| `Permission\Handler`, `accessGranted` | Security voters (`icms_perm:`, `icms_system_admin:`) | exists, reused |
| handler events | stay; forwarding to EventDispatcher via `LegacyEventForwarder` | later |
| Doctrine entity of a 3.x module | ORM `ClassMetadata` → `DoctrineDefinitionProvider` + `DoctrineStore` | this change (read) |
| table schema from `icms_version.php` | DBAL Schema from `EntityDefinition` | later |

## Goals / Non-Goals

**Goals:**
- One description of an object that forms, lists and the API can all consume, with zero changes to existing objects.
- Lists, filters and a read API for an object without controller code or per-object configuration beyond one manifest line for the API.
- Prove on mimetype that the new list pipeline returns exactly what the legacy table returns.
- Prove that an ORM entity can feed the same description.

**Non-Goals:**
- Replacing the IPF handler as the persistence gateway of existing objects.
- Writing through the API, forms for ORM-defined objects, schema generation.
- Changing `htdocs/libraries/Icms/`.
- Making Twig the default renderer.

## Decisions

### D1. Own mapping for existing objects; no ORM underneath
The handler stays the only persistence gateway for existing IPF objects, so handler events, permission criteria, `getVar` formats and the HTML input filter keep working.
*Alternative*: map IPF objects onto Doctrine ORM entities. Rejected: the ORM needs classes with real properties (IPF keeps values in `$vars`), the unit of work conflicts with `handler->insert()` writing immediately, and each object would have two definitions. DBAL stays the shared connection.

### D2. `EntityDefinition` behind a provider interface
`App\Ipf\Metadata\EntityDefinition` (module, item, table, key field, identifier field, summary field, fields, permissions) and `FieldDefinition` (name, data type, required, max length, caption, description, control, persistent, sortable, multilingual, shown on form, read-only) are immutable value objects. `DefinitionProviderInterface::supports()/definition()` has two implementations:
- `LegacyDefinitionProvider` creates one blank object with `handler->create()` (as `Table::$_tempObject` does) and reads `$vars`, controls, key/identifier names and `handler->getPermissions()`.
- `DoctrineDefinitionProvider` reads ORM `ClassMetadata` (D7).

`DefinitionRegistry` resolves `module/item` to a definition and memoises it for the request. Forms keep reading the *instance* (objects may change their fields per instance with `hideFieldFromForm()`, `setControl()`); the cached definition serves lists and the API only.
*Alternative*: PHP attributes as a new declaration style. Deferred: the provider interface leaves room for it without touching consumers.

### D3. `ListQuery` with two request dialects
`ListQuery` (search text, filters, sort field, order, offset, limit) is store-neutral. `ListQueryResolver` reads it from a `Request` in one of two dialects:
- *legacy*: `<item>_sortsel`, `<item>_ordersel`, `start<key>`, `limitsel`, `filtersel`, `filtersel2`, `quicksearch_<id>` (the names `Table` uses);
- *api*: `q`, `sort`, `order`, `page`, `limit`, `filter[<field>]`.

Sort, filter and search fields are checked against the definition; an unknown or non-persistent field raises `InvalidListQueryException` (HTTP 400 in the API). The limit is capped (API: 100, default 15).

### D4. Stores: handler criteria or ORM query
`ObjectStoreInterface::find(id)`/`list(ListQuery): ListResult{items,total}`.
- `HandlerStore` translates the query into `Icms\Db\Criteria\Compo` and calls `handler->getObjects()`/`getCount()`. No SQL of its own.
- `DoctrineStore` builds an ORM `QueryBuilder` with bound parameters and uses the ORM paginator.

`IpfRepository` picks the store that belongs to the definition. An object belongs to exactly one store.
*Alternative*: an own DBAL query builder for IPF objects. Rejected: it would bypass handler behaviour (granted-objects criteria, joins the handler adds).

### D5. Table model and renderer interface
`TableFactory::create(handler|definition, ?TableConfig)` returns a `TableConfig` with defaults from the definition (columns, quick-search fields, actions, intro buttons, custom column methods, filters). `TableRendererInterface::render(TableConfig, Request): string`:
- `LegacyTableRenderer` configures a legacy `Icms\Ipf\View\Table` from the config and returns `fetch()`. Output is byte-identical to today, cookies and theme overrides of `system_persistabletable_display.html` keep working. This is the default.
- `TwigTableRenderer` resolves a `ListQuery` (legacy dialect), asks `IpfRepository`, builds a `TableView` (columns, rows, sort state, paging) and renders `templates/ipf/table.html.twig`. Uses GET and no cookies. Opt-in per page.

*Alternative*: reproduce the Smarty variable contract of `Table::render()` in a new renderer now. Rejected for this change: 800 lines of behaviour to copy with no user-visible gain; the adapter gives parity for free and a test guards that both paths return the same rows.

### D6. Opt-in, read-only API
`App\Controller\Ipf\IpfApiController`, routes `GET /api/ipf/{module}/{item}` and `GET /api/ipf/{module}/{item}/{id}`, `#[RequiresLegacy(stage: 'module')]`. `api` is added to `icms.legacy.reserved_prefixes`.

Exposure comes from the module manifest, read by `ResourceRegistry`:

```php
$modversion['object_api']['mimetype'] = [
    'read' => 'icms_system_admin:mimetype', // a security attribute; required
    'search' => ['name', 'extension', 'types'], // fields `q` searches; default: the identifier
    'fields' => [...],                          // optional whitelist; default: persistent fields
];
```

An object without an entry answers 404, as does an unknown module or item. `read` is checked with the Symfony authorization checker, so existing voters (`icms_perm:…`, `icms_system_admin:…`, `ROLE_…`) apply; denial answers 403 (401 for anonymous visitors).

Responses are JSON through the Serializer and `EntityNormalizer`: `{"items": [...], "total": n, "page": p, "limit": l}` and a single object for detail. Values are typed raw values (`getVar($key, 'n')`, integers cast, array types as arrays), plus `id`, `title` and `url` (`getItemLink(true)`). Custom column methods are not executed.
*Alternative*: API Platform. Rejected: it presumes ORM entities or hand-written state providers and brings its own configuration surface.

### D7. ORM as a second definition source
`doctrine/orm` is added with attribute mapping for `src/Ipf/Example/` in the test environment only. `DoctrineDefinitionProvider` maps ORM field types to IPF data types (string → `TXTBOX`, text → `TXTAREA`, integer/smallint/bigint → `INT`, float/decimal → `FLOAT`, simple_array → `SIMPLE_ARRAY`, everything else → `OTHER`), takes key and length from the mapping and nullable for `required`, and resolves captions with the IPF convention `_CO_<MOD>_<ITEM>_<KEY>`. `TablePrefixListener` (`loadClassMetadata`) prefixes table names with the ImpressCMS table prefix.

The example entity `App\Ipf\Example\Mimetype` is `readOnly` and mapped on the existing `system_mimetype` table, registered as `system/mimetype_orm`. It exists to prove the path; it is not shipped behaviour.
*Alternative*: leave the ORM path as a paper design. Rejected by the project owner: the provider interface should be proven with two real implementations.

## Risks / Trade-offs

- [Modules read `$obj->vars`, `$handler->_itemname`, `keyName` directly.] → Nothing is removed; the definition is a read-only derivative.
- [Objects that change their definition per instance differ from the cached definition.] → Forms keep reading the instance; the cache serves lists and the API.
- [Two list paths (legacy adapter and new pipeline) can drift.] → A test compares row ids of both for the default list, a sorted list and a quick search.
- [Custom column and filter methods on handlers return HTML.] → `TableConfig` carries them for both renderers; the API never executes them.
- [The API returns raw values, not sanitised HTML.] → Documented; consumers escape. The `fields` whitelist limits exposure.
- [Definitions and voters need a booted legacy runtime, and the admin module launch can `exit()`.] → API routes boot to `module` without `ICMS_IN_ADMIN`.
- [Handler and EntityManager writing the same table would leave a stale identity map.] → One store per object; the example entity is read-only.
- [The ORM schema tool would want to alter legacy tables.] → Not used; documented; the example entity is test-only.
- [`doctrine/orm` may not install against the pinned DBAL/bundle versions.] → First task of the ORM group; if it needs changed pins, stop and ask.

## Migration Plan

1. This change: metadata, list pipeline, renderers, read API, ORM provider (PoC mimetype).
2. The other five IPF system pages on the same pieces; `ControlMap` on `FieldDefinition`; missing controls.
3. API writes (Form + Persister + token); `internallink.php` becomes a thin wrapper around the API (URL kept, deprecation notice).
4. A generic admin controller for modules (`object_items` → page without `admin/*.php`); legacy `Table`/`Controller` marked `@deprecated`, still working.
5. Twig as the default renderer; schema from the definition through DBAL; optional attribute declaration style; forms and writes on the ORM path.

**Rollback**: `MimetypeController::list()` is the only changed behaviour path and its output is unchanged; `ICMS_TAKEOVERS_DISABLED=system.mimetype` still serves the legacy page. The API is additive.

## Open Questions

None that change what is built in this change.

## Implementation Notes

Where the implementation differs from, or adds to, the decisions above:

- **Table id (D2, D5).** The description's class name is the handler's `className`, which `Icms\Ipf\View\Table` uses as the table id (`quicksearch_<id>`, cookies), not the PHP class name, which may differ in case.
- **Legacy table and the request (D5).** `Icms\Ipf\View\Table` reads its sort options with `filter_input()`, so an in-process test cannot hand it a sorted request; the tests give both sides the sort as the table's default, and the real request stays covered by the HTTP snapshots. The legacy page navigation echoes a stylesheet link when no theme is active, which a page collects in its output buffer as before.
- **Table filters (D5).** `TableConfig` carries columns (with custom methods), quick search, intro buttons, actions and the default sort. The filter drop-downs of the legacy table (`addFilter()`), custom actions and "with selected" actions are not carried yet; a list query does accept filters (`filtersel`/`filtersel2`, `filter[<field>]`).
- **Public services (D5).** The registry, resolver, repository, table factory and both renderers are public, so that module pages that are still legacy scripts can fetch them.
- **API item shape (D6).** An item is `{id, title, url, fields: {...}}`: the fields are nested, so a field named `id`, `title` or `url` cannot collide. `title` is the identifier field's value.
- **Filter markers (D6).** Text stored through the legacy HTML input filter ends in `<!-- filtered with htmlpurifier --><!-- input filtered -->`. Both stores leave these markers out of typed values; they are bookkeeping, not part of the value.
- **Check order (D6).** The controller looks up the manifest entry, then the permission, and only then builds the description. Describing an object needs its class to load on its own: the system module's admin objects other than mimetype use language constants that only their admin page loads (`_CO_ICMS_ADSENSE_DESCRIPTION`, ...). They must be fixed before they are listed or exposed; this belongs to the follow-up for the other system pages.
- **405 (D6).** The API's addresses have explicit routes for the other methods, answering 405 with `Allow: GET` as JSON.
- **ORM declaration (D7).** `#[App\Ipf\Attribute\IpfObject(module, item, identifier, summary, captionPrefix, language, adminLanguage)]` marks an ORM-mapped class as a module object. The column type `icms_simple_array` (`App\Ipf\Store\Dbal\PipeArrayType`) stores a list as IPF does (`a|b|c`); Doctrine's `simple_array` joins with commas.
- **ORM and the container (D7).** With the ORM configured, its cache warmers and validator auto-mapping loader create the entity manager while the container is built or shut down, which opens the DBAL connection and boots ImpressCMS (or fails where no site is installed). `App\DependencyInjection\DoctrineWarmupPass` removes those tags; proxies and metadata are built on first use.
- **ORM in the API.** `ResourceRegistry` reads module manifests only, so an ORM-defined object is exposed by the same `object_api` entry of its module. The test-only example entity has no entry and is not served by the API.
- **Dependencies.** `doctrine/orm` 3.3 installs against the pinned `doctrine/dbal` 4.2 and `doctrine/doctrine-bundle` 2.13 without changing them.
