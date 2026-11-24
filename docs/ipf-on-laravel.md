# IPF resources on Laravel

How an ImpressCMS IPF resource gets a Laravel implementation without a
second framework beside Laravel, and without changing the modules that have
none. The system mimetype administration is the first resource migrated this
way. To migrate another one, see [migrating-a-resource.md](migrating-a-resource.md).
The host itself is described in [laravel-host.md](laravel-host.md).

## Principles

- **The IPF declaration is the single source of semantics.** It covers the
  entity's `initVar()` calls and controls, the handler's key, table,
  permissions and events, and metadata attributes on the handler class.
  Laravel artifacts are generated from it and never edited.
- **Modules are the unit.** A module's Laravel side lives in
  `app-modules/<dirname>/` and follows the module's state in ImpressCMS. A
  module without a Laravel side is untouched: its URLs go through the legacy
  bridge as before.
- **Every concern can be rolled back separately.** Ownership of the URL,
  rendering, validation, persistence and authorization each have a switch,
  and none needs a code change.
- **Standard Laravel only:**
  - container
  - cacheable routes
  - middleware
  - FormRequest and Validator
  - Policies and Gate
  - Blade components
  - Eloquent
  - migrations
  - Artisan

## The flow

```
IPF entity + handler + #[Icms\Ipf\Metadata\…]           (htdocs, legacy)
        │  App\Ipf\Definition\DefinitionReader            (reads; never mutates)
        ▼
App\Ipf\Definition\ResourceDefinition                   (framework-neutral, with provenance)
        │  php artisan icms:definitions:compile
        ▼
app-modules/<dirname>/Generated/<Item>/                 (committed; check fails on drift)
   <Item>Descriptor  <Item>Request  <Item>Policy  <Item>  <Item>Schema
        │
        ▼
module provider → resource registry, takeovers, policies, views
        │
request → route (cacheable) → LegacyScriptController → TakeoverRegistry
        → LegacyOperationsController (op → action) → resource controller
        → FormRequest / Policy / IpfResourceRepository / Blade or legacy views
```

## 1. Canonical definition — `app/Ipf/Definition/`

`DefinitionReader` reads a live handler and an entity it created.
- **What it copies.** It copies `vars` and `controls` and reads the attributes
  by reflection. It never calls `getForm()` or `Table::render()`, which set
  controls and read the request.
- **Defaults.** A variable without a control gets the control that
  `Icms\Ipf\Form\Base::createElements()` would give it (`DataType::defaultControl()`).
- **What it produces.** The result holds:
  - fields: type, default, required, maxlength, persistence, form visibility,
    read-only, sorting, caption, description, control, option provider
  - permissions
  - primary key, table (without the installation prefix), module and item
  - list metadata, explicit relations, the legacy URL and notifications
  - provenance: the entity and handler classes, and the handler's file and
    line relative to the web root
- **Refusals.** It raises `UnsupportedDefinitionException`, naming the
  declaration, for:
  - unknown variable keys, control keys or control names
  - selects without choices
  - composite keys
  - tables without the prefix
  - metadata that names unknown variables
- **No inferred relations.** An `itemHandler` control is an option provider,
  never a relation.

Metadata IPF did not have is declared with attributes on the handler class
(`htdocs/libraries/Icms/Ipf/Metadata/`). Legacy code never reads them.

| Attribute | Declares |
|---|---|
| `ListView` | row actions, quick-search fields, create button, default sort, order and page size |
| `ListColumn` (repeatable) | list columns: field, alignment, width, sortable |
| `ListFilter` (repeatable) | list filters: field and the handler method listing the choices |
| `Relation` (repeatable) | an explicit relation: field, target resource, cardinality, ownership, foreign key, join table |
| `LegacyOperations` | the legacy admin URL (`script`, `match` parameters) and its `op` values (`form`, `save`, `delete`, `confirm`) |
| `Notification` (repeatable) | a notification event and the handler event it follows |

`DefinitionCompilerTest` checks the mimetype attributes against
`admin/mimetype/main.php`, so the copy cannot drift.

## 2. Generation — `php artisan icms:definitions:compile` / `:check`

`App\Ipf\Compiler\DefinitionCompiler` boots the legacy runtime and reads the
definition of each registered resource. `ArtifactGenerator` then writes one
directory per resource, `app-modules/<dirname>/Generated/<Item>/`. Every file
carries a `GENERATED FILE - DO NOT EDIT` header naming the generator, the
resource and the source declaration. The output depends only on the
definition: no captions, no table prefix, no absolute paths, no timestamps.
The same definition gives the same bytes.

| File | Laravel artifact | From |
|---|---|---|
| `<Item>Descriptor` | data: fields, form order, permissions, list, legacy URL, relations, notifications, support | everything |
| `<Item>Request` (`App\Ipf\Http\IpfFormRequest`) | `rules()` and message templates; captions resolved at runtime | controls, types, required, maxlength, permissions |
| `<Item>Policy` (`App\Ipf\Auth\IpfResourcePolicy`) | abilities `viewAny`…`delete` and `permission` | the legacy URL (system admin function) or module, handler permissions |
| `<Item>` (`App\Ipf\Eloquent\IpfModel`) | read-only Eloquent model: table, key, casts | table, key, data types |
| `<Item>Schema` (`App\Ipf\Schema\SchemaContract`) | the column contract | persistent fields, typed as `Icms\Db\Legacy\Updater\Handler` types them |

- **Drift check.** `check` regenerates in memory and fails, naming each file,
  when one is missing, differs or is no longer generated. CI runs it after
  installing the test site. Committed files are what deployments use: the web
  never compiles, and modules without a Laravel side never need the compiler.
- **Unsupported controls.** A control without a validation rule (uploads,
  editors, dates, users, …) means the resource gets no FormRequest; the
  descriptor records the field and control. A control without a Blade
  component marks the resource as not renderable with Blade. Such a resource
  can only be served with the legacy renderer and legacy validation, which
  the resource factory enforces. Nothing is coerced to a text input.

## 3. Modules — `app-modules/<dirname>/`

| File | Role |
|---|---|
| `<Dirname>ModuleServiceProvider.php` | extends `App\Module\ModuleServiceProvider`; found by `App\Providers\ModulesServiceProvider` |
| `resources.php` | the compiled resources: `item => [handler, controller, middleware]` |
| `config.php` | the module's switches, as `config('icms_modules.<dirname>')` |
| `Generated/` | compiler output |
| `Http/`, `View/`, `views/` | hand-written controllers, legacy view adapters, Blade views (`<dirname>::…`) |
| `routes.php`, `migrations/` | loaded when present |

The trust path's own `modules/` directory belongs to legacy (Protector,
duplicatable modules), hence `app-modules/`.

Module state is read per request (`App\Module\ModuleRegistry`: the `modules`
row, and `ICMS_MODULES_LARAVEL_DISABLED`), never baked into the route cache.
An inactive module's takeovers fall through to legacy, and its routes behind
`icms.module:<dirname>` answer 404.

## 4. Routing — standard and cacheable

- **One route per taken-over script.** `routes/web.php` registers an ordinary
  route for each legacy script path that a takeover claims, for example
  `GET|POST /modules/system/admin.php`, to
  `App\Http\Controllers\LegacyScriptController`.
- **Variant selection.** The controller asks `App\Takeover\TakeoverRegistry`
  for the owner. A takeover matches on:
  - query parameters
  - POST-body-else-query parameters (as `admin.php` extracts them)
  - methods
  - `TakeoverCondition`s
  - its switch and its module's state

  The match runs behind the takeover's middleware
  (`App\Takeover\MiddlewareDispatcher`: the router's middleware resolution
  and `Illuminate\Routing\Pipeline`). Everything else goes to the legacy
  bridge, exactly as if no route claimed the URL.
- **Why the registry.** Laravel's route matcher, compiled or not, selects by
  path and method only. The query/body decision is therefore the one thing
  the registry does, behind one standard route. It is not a router.
- **405 and fallback.**
  - A method a takeover does not accept answers 405 for its variant only;
    other variants keep their legacy behaviour
    (`App\Routing\RouteOwnership`).
  - Unclaimed paths fall back to the bridge.
  - Reserved prefixes answer 404.
  - Static assets are served by the bridge.
- **Testing.** `php artisan route:cache` works. The whole Http suite passes
  with cached routes, and CI runs it that way. `tests/Http/RouteCacheTest.php`
  covers the ownership matrix with the cache.
- **Legacy operations.** `App\Ipf\Http\LegacyOperationsController` turns the
  legacy `op` URL of an IPF resource into the actions of its resource
  controller, using the `op` values of `#[LegacyOperations]`. The controller
  never sees query-string conventions.

| op | key | action | middleware |
|---|---|---|---|
| none | | `index` | |
| `form` | no / yes | `create` / `edit` | |
| `save` | posted no / yes | `store` / `update` | `icms.token:legacy` |
| `delete` | | `confirmDestroy` | |
| `delete` + `confirm` | | `destroy` | `icms.token:legacy` |

**Middleware groups.**
- Legacy URLs keep the legacy stack: no Laravel session or CSRF, and the
  `TrimStrings`/`ConvertEmptyStringsToNull` normalization stays removed.
- Migrated pages under a legacy URL run their own middleware: `icms.module`,
  `icms.admin` and `icms.token:legacy`.
- The `icms` group (ImpressCMS session, shared errors, bindings) serves
  Laravel-only routes that need the identity.
- Laravel's `web` group (cookie encryption, Laravel session, CSRF) is not
  used yet. A resource moves to it only once its session, CSRF, redirects and
  old bookmarks are covered; see *Next steps*.

## 5. The mimetype slice

| Concern | Implementation |
|---|---|
| URL | `/modules/system/admin.php?fct=mimetype&op=…`, GET and POST, `fct` in the query or the body |
| controller | `Modules\System\Http\MimetypeController`: `index`, `create`, `edit`, `store`, `update`, `confirmDestroy`, `destroy`; no `icms::`, no handler lookups, no form objects |
| validation | `MimetypeRequest`. It reads only the top-level POST body under the legacy names. An unsent key is not validated and not written, so an unsent multi-select keeps its value and an unsent permission removes all groups. Messages use the legacy wording (`_XOBJ_ERR_REQUIRED`) with the caption in the current language. A failure throws `LegacyFormRejected`, which answers as the legacy save does |
| authorization | `MimetypePolicy`, via `IpfResource::authorize()`, behind the `icms.admin:mimetype` middleware: module admin plus the function's category, webmaster bypass; `permission` reads `group_permission` |
| persistence | `App\Ipf\Repository\LegacyHandlerRepository`: `setVar` for the given values, the handler's `insert()` (`cleanVars`, events in legacy order), then `Icms\Ipf\Permission\Handler::saveItem_Permissions()`; `delete()` removes the permissions. With `eloquent-read`, `EloquentReadRepository` reads with the generated model and `group_permission`, and writes through the handler |
| rendering | Blade components `resources/views/components/ipf/` (`<x-ipf::field>` dispatches `text`, `integer`, `textarea`, `select`, `multiselect`, `hidden`, `readonly`, `permission`; plus `row`, `description`, `errors`, `quicksearch`, `filters`, `list-table`, `row-actions`, `pagination`, `delete-confirm`), in the admin theme. Forms post the legacy names to the legacy URL with `op` and a token. An unknown control throws instead of rendering. With `renderer=legacy`, `Modules\System\View\MimetypeLegacyViews` renders the IPF table and form exactly as `main.php` does |
| schema | `MimetypeSchema` against the live table: `php artisan icms:schema:diff system.mimetype` reports only that the key is a `KEY`, not a `PRIMARY KEY` |

## 6. Schema — `php artisan icms:schema:diff [resource] [--migration]`

`App\Ipf\Schema\SchemaDiff` compares the contract with the live table, by type
family, as the legacy updater types variables. The install SQL often differs:
narrower varchars, `KEY` instead of `PRIMARY KEY`.

| Finding | Kind | Action |
|---|---|---|
| same family, nullable, extra legacy column, key not primary, narrower legacy type | compatible | reported |
| missing column, missing join table of an explicit relation | additive | `--migration` writes an expand-only migration into the module's `migrations/` |
| other type family, key not indexed or not auto-increment, missing table | incompatible | reported; exit 1 |

- **What a migration does.** Generated migrations only add columns
  (`NOT NULL` with the updater's defaults) and join tables. `down()` removes
  only what `up()` added.
- **When they run.** Migrations run with `php artisan migrate`, never in a
  web request.
- **What stays.** `Icms\Db\Legacy\Updater` remains how unmigrated modules
  update their tables.
- **Order of changes.** Expand first, then backfill, then let legacy and
  Laravel share the compatible shape. Contract only in a later release, by a
  hand-written migration, once no legacy code uses what it removes.
- **Test.** `SchemaDiffTest` applies a generated migration and checks that the
  legacy handler still stores and reads the table.

## 7. Rollback

Each switch restores one concern without reverting code. Switches are read
through `config()`; after `config:cache`, run `config:clear` when you change
one.

| Unit | Switch | Effect |
|---|---|---|
| URL ownership (controller takeover) | `ICMS_TAKEOVERS_DISABLED=system.mimetype` | the exact legacy URL runs `admin/mimetype/main.php` again (`MimetypeLegacyCharacterizationTest`) |
| a whole module | `ICMS_MODULES_LARAVEL_DISABLED=system` | every takeover of the module falls through; its routes 404 |
| Laravel rendering | `ICMS_SYSTEM_MIMETYPE_RENDERER=legacy` | the IPF table and form of `main.php`, byte-identical to the legacy snapshots apart from the form's token (`MimetypeCharacterizationTest`) |
| FormRequest validation | `ICMS_SYSTEM_MIMETYPE_VALIDATION=legacy` | submitted keys pass unvalidated; only the entity's `cleanVars()` checks them, as in legacy |
| Eloquent reads | `ICMS_SYSTEM_MIMETYPE_PERSISTENCE=legacy` (default) | reads through the IPF handler; writes always do |
| generated policy | `ICMS_SYSTEM_MIMETYPE_POLICY=legacy` | only the `icms.admin` middleware decides access, as `admin.php` does |
| schema | none needed | the initial phases only add; drop an added column with the migration's `down()` |

## 8. Limits

### Unsupported IPF controls and behaviours

- **Validation and Blade.** Validation rules and Blade components exist for
  `text` (TXTBOX, INT, FLOAT, CURRENCY), `textarea`, `select`, `selectmulti`,
  `hidden`, `label` (read-only) and handler permissions.
- **No adapter yet.** These controls have neither a rule nor a component;
  resources using them stay on the legacy renderer and legacy validation:
  - `yesno`, `radio`, `checkbox`
  - `color`, `date`, `datetime`, `time`
  - `user`, `user_multi`, `group`, `group_multi`
  - `image`, `imageupload`, `file`, `fileupload`, `upload`, `richfile`, `urllink`
  - `source`, `dhtmltextarea`
  - `page`, `parentcategory`, `blockoptions`, `autocomplete`, `language`,
    `theme`, `theme_multi`, `timezone`, `country`, `password`, `passwordtray`,
    `signature`, `section`
  - module-specific controls
- **Behaviour not ported.** These stay on the legacy handler and are not
  reproduced on the Laravel side:
  - uploads (`enableUpload`, `uploadMedia`)
  - SEO/metagen fields
  - multilingual fields
  - `initCommonVar()` variables
  - composite keys
  - `object`-based option providers whose object changes per request
  - list filters with state (`filtersel2`)
  - the legacy table's cookie writes (cookies are read, not written)
  - notifications: declarable, but nothing triggers them yet
- **Eloquent.** The models are read-only; writes stay on the handler. There
  are no observers yet that would replace `cleanVars()`, the handler events
  or the permission storage.

### Explicit assumptions

- **Definitions read at compile time.** The runtime definition (entity
  constructor plus handler) is the same in the console as in a web request.
  Definitions that change by request state are not supported; the
  `customtag` and `blocksadmin` scripts change controls per type, for
  example.
- **Captions are not compiled.** Captions and descriptions are language
  strings, resolved from the live entity at runtime.
- **Tables have the installation prefix.** Handlers that override `table`
  after their constructor still carry the prefix.
- **The mimetype list attributes restate `main.php`.** A test pins them to it.
- **One provider per module.** A module's Laravel side has one provider,
  named after its directory.

### Compatibility risks

- **HTML differs from legacy.** The Blade pages differ in markup from the
  legacy pages. Themes or scripts that target the legacy IPF markup (CSS,
  JavaScript selectors) see other markup. Rollback: the renderer switch.
- **Different redirect for unknown items.** On `edit` with an unknown id the
  controller shows the create form, as legacy does. On `destroy`, the token
  is checked before the "not selected" answer, which legacy gives first.
- **Legacy's maxlength bug.** `quickInitVar()` never sets a maxlength (its
  type check compares with a string), so no length rule is generated for
  mimetype fields even where an install created narrower columns. The schema
  diff reports such columns as compatible but narrower.
- **Unvalidated hidden fields.** Hidden, non-key fields are written without
  validation, as `postDataToObject()` writes them.
- **Middleware on the legacy URL.** Takeover middleware (`icms.admin`) runs
  inside `LegacyScriptController`, not as route middleware, so
  `route:list` does not show it.
- **Module state queries the database.** Reading the state costs one query
  per request that could be taken over.

### Recommended next migrations

1. `system/userrank`: a small entity; needs `yesno` and an `image` adapter
   or the legacy renderer.
2. `system/autotasks`: INT/TXTBOX, `yesno`, `source`.
3. `system/customtag`: needs per-type control switching expressed
   statically first.
4. Before any of these: a `yesno` component and rule, and moving a migrated
   resource to Laravel's `web` group once its session, CSRF, redirects and
   old bookmarks are covered.
