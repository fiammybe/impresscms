## Context

See `proposal.md` (Why). This change builds on the unarchived change `ipf-on-symfony-mimetype-pilot`, whose pieces it reuses unchanged: `#[SystemAdminFunction]` with `SystemAdminContext` and `SystemAdminAccess` (admin context, category rights, legacy denials), `AdminPageRenderer` (admin chrome without `exit`), `LegacyRedirect` (`redirect_header()` without `exit`) and `LegacyOutput`. The specs `request-ownership`, `legacy-request-bridge`, `shared-runtime` and `host-deployment` apply.

How preloads work today:

- `icms::boot()` creates `Icms\Preload\Handler::getInstance()` **first**, before the database, the configuration and the session, and triggers `startCoreBoot` on it. `LegacyRuntime` maps it to the `services` stage.
- The handler lists `ICMS_PRELOAD_PATH` (`plugins/preloads`) with `Icms\Core\Filesystem::getFileList(…, ['php'])`, `include_once`s each file, instantiates `IcmsPreload<Ucfirst(name)>` and attaches every `event*` method to `Icms\Event` under the lower-cased event name.
- `$icmsOnDemandPreload` (module preloads) is read but set nowhere. `Icms\Preload\LibrariesHandler` is a separate mechanism (`icms.library.<lib>.php`).
- There is no legacy page for preloads and no stored state.

How the system admin menu works today: `modules/system/menu.php` scans `modules/system/admin/*/xoops_version.php` (or `icms_version.php`), and for each function with `hasAdmin` builds an entry linking to `admin.php?fct=<dir>` with `id` = its `category`. `icms_cp_header()` caches the result in `cache/adminmenu_<language>.php` and filters the entries by the user's `system_admin` item ids. The group administration page offers the same categories as `system_admin` rights.

## Goals / Non-Goals

**Goals:**
- A first Symfony admin page without a legacy counterpart, on the mimetype pilot's pieces, under a clean URL.
- Disabling preloads with one small, inert seam in `Icms\Preload\Handler`.
- No behaviour change for sites that never disable a preload.

**Non-Goals:**
- Characterization snapshots: there is no legacy page to compare with. The page gets its own HTTP tests.
- A takeover switch (`ICMS_TAKEOVERS_DISABLED`): the route claims no legacy URL, so there is nothing to fall back to.
- Symfony Form for the toggle: one hidden field per button does not justify the form layer; the request is validated by hand (D6).

## Decisions

### D1. Route `/admin/preloads`, admin context through the existing attribute
`App\Controller\System\PreloadController`, marked `#[SystemAdminFunction('preloads')]`, with two routes:
- `GET /admin/preloads` (`system_preloads`): the list
- `POST /admin/preloads/toggle` (`system_preloads_toggle`): enable or disable one preload

`SystemAdminContext` already hydrates the request as `modules/system/admin.php`, so the module launch finds the system module, `ICMS_IN_ADMIN` is set, `cp_functions.php`, `constants.php` and the language file `system/admin/preloads` are loaded, and the category right is checked with the legacy denials. Nothing in it depends on the URL.

At routing level 1 only `/index.php/<route>` reaches the host, so links are generated through the router with the base URL the request has: from a Symfony page that is `/admin/preloads` or `/index.php/admin/preloads`, whichever the current request used.
*Alternative*: claim `/modules/system/admin.php?fct=preloads` as the mimetype page does. Rejected by the user in favour of a URL of its own; it would also leave a takeover without a legacy page to fall back to.
*Alternative*: under the reserved `admin-next` prefix. Rejected: that prefix is a test-environment reservation (`icms.legacy.reserved_prefixes`), not a production namespace.

### D2. Registration as a system admin function with an external link
- `modules/system/constants.php`: `define('XOOPS_SYSTEM_PRELOADS', 25)`.
- `modules/system/admin/preloads/xoops_version.php`: name `_MD_AM_PRELOADS`, `category` `XOOPS_SYSTEM_PRELOADS`, group `_MD_AM_GROUPS_SYSTEMTOOLS` (with autotasks and version), image `preloads.png`, and a new optional key `'link' => 'index.php/admin/preloads'`, relative to `ICMS_URL`.
- `modules/system/menu.php`: uses `ICMS_URL . '/' . $modversion['link']` when the key is present, otherwise `admin.php?fct=<dir>` as today. Additive: existing functions have no `link`.
- `modules/system/admin/preloads/main.php`: for `admin.php?fct=preloads`, sends `Location: ICMS_URL/index.php/admin/preloads` and exits. That URL works at every routing level, which is why the menu link uses it too.

Because the category is a regular `system_admin` item, the menu filter, the dashboard, the module options list and the group administration page pick it up without further changes.
*Alternative*: inject the menu entry from Symfony through an event. Rejected: the menu is cached as a file by legacy code and filtered there; a legacy registration is the smallest change that keeps every consumer consistent.

### D3. Disabled set in `<trust>/var/preloads.php`
A PHP file returning `['disabled' => ['debug_mode', …]]`. Reasons:
- The handler runs before the database exists, so a config item or table cannot be read in time.
- `<trust>/var/` is outside the web root, per site, writable by the host already (`var/cache`, `var/log`), and not touched by `cache:clear` (only `var/cache/<env>`), by ImpressCMS' cache clearing or by replacing `plugins/preloads/*`.
- A PHP array is read through OPcache at no measurable cost on every request.

Path: `ICMS_TRUST_PATH . '/var/preloads.php'`, which is the host's trust path since `relocate-host-into-trust-path`.

`App\Preload\DisabledPreloads` reads and writes it: names are validated against `^[A-Za-z0-9_-]+$` and against the inventory (D5), sorted and de-duplicated; the file is written to a temporary file in the same directory and `rename()`d over the old one, followed by `opcache_invalidate()`. A failed write throws, and the controller reports it (spec: storage problems).
*Alternative*: rename the file to `<name>.php.disabled`. Rejected: needs a writable `plugins/preloads`, an update of ImpressCMS restores the file and silently re-enables it, and protector's installer checks for `protector.php` by name.
*Alternative*: a config item in the database read lazily. Rejected: the preloads are attached before the database exists, and `startCoreBoot` handlers would still run.

### D4. `Icms\Preload\Handler` skips disabled preloads
The constructor reads the disabled set before the file loop and skips listed names, before `include_once`:

```php
$disabled = self::disabledPreloads();
foreach ($preloadFilesArray as $filename) {
    if (in_array($filename, $disabled, true)) {
        continue;
    }
    …
}
```

`disabledPreloads()` returns `[]` when `ICMS_TRUST_PATH` is undefined or empty, when the file is missing, or when it does not return an array with a list under `disabled`; it suppresses nothing else and never throws (spec: disabling never fails a request). The library keeps no dependency on the host: it only knows the file's path and shape, which `App\Preload\DisabledPreloads` documents as the other side of the contract. On-demand preloads are not filtered (out of scope).

### D5. Inventory without running disabled code
`App\Preload\PreloadInventory::all(): list<PreloadInfo>` lists `ICMS_PRELOAD_PATH/*.php` the same way the handler does (same `getFileList` call, same name derivation) and returns, per preload, its name, class name, enabled state and events. Events are read with `token_get_all()`: the public methods named `event*` of the class `IcmsPreload<Name>`, converted to the event name as `addPreloadEvents()` does, displayed in their original camel case (`eventBeforeFooter` → `beforeFooter`). No preload file is included, so disabled preloads show their events without running.
*Alternative*: reflection on the loaded classes. Rejected: disabled preloads are not loaded, and including them only to inspect them would run their file-level code.

### D6. Toggle request and token
The list renders one form per preload, posting to `system_preloads_toggle` with `preload=<name>`, `state=enable|disable` and `icms::$security->getTokenHTML()`. The controller:
1. checks `icms::$security->check()`; on failure, `LegacyRedirect::to(<list url>, 3, _CORE_TOKENINVALID + errors)`
2. checks that `preload` is in the inventory and `state` is one of the two values; otherwise redirect with `_MD_AM_PRELOADS_UNKNOWN`
3. updates `DisabledPreloads`; on a write failure, redirect with `_MD_AM_PRELOADS_NOTWRITABLE` naming `<trust>/var`
4. redirects with `_MD_AM_PRELOADS_DISABLED`/`_ENABLED`

The toggle route only accepts POST, so GET answers 405 and changes nothing. `LegacyRedirect` keeps the site's `use_custom_redirection` behaviour and the same-site check.

### D7. Rendering: Smarty content template in the admin theme
`AdminPageRenderer::render()` with a content template `system_adm_preloads.html` shipped in `modules/system/templates/admin/preloads/`, addressed as a `file:` resource so that no system module update is needed to install it into `tplfile`. It shows a table (name, events, state, action button) in the markup of the other system admin tables, plus an explanation that changes apply from the next request. Strings come from `language/<lang>/admin/preloads.php`.
*Alternative*: a `db:` template registered in the system module's `xoops_version.php`. Rejected for now: it only works after the administrator updates the system module, and the page must work right after deploying. Revisit with the Twig rendering follow-up.

## Risks / Trade-offs

- [Disabling `protector` or another security preload weakens the site.] → The page shows a warning next to preloads whose name is in a short "security" list (`protector`), and the confirmation message repeats it. Not blocked: the administrator owns the decision.
- [Disabling a preload the admin page itself needs could lock the administrator out.] → No core admin functionality depends on a preload; `autologin` and `userinfo` only add conveniences. Recovery without the page: delete `<trust>/var/preloads.php`.
- [The `file:` template resource may be refused by the ImpressCMS Smarty security settings.] → Verified in the first implementation task; fallback is registering the template under `db:` and compiling it from the file when the row is missing.
- [The admin menu is cached in `cache/adminmenu_<language>.php`; existing sites do not see the entry until it is rebuilt.] → The system module update of this release bumps the system module's database version, and its update step deletes `cache/adminmenu_*.php`; `icms_cp_header()` rebuilds a missing menu on the next admin page.
- [`token_get_all()` parsing differs from what PHP attaches.] → A test compares, for every shipped preload, the parsed events with the events the handler attaches when the preload is enabled.
- [Two administrators toggling at the same time.] → Last write wins on the whole file; the read-modify-write is guarded by `flock()` on a lock file next to it.

## Migration Plan

1. Library seam (D4) with tests: no file, empty file, corrupt file, disabled name, unknown name.
2. Host pieces (D3, D5) with unit tests.
3. Registration (D2), language files for every language (the language consistency test requires the English constants everywhere), menu link support.
4. Controller and template (D1, D6, D7), HTTP tests at routing levels 1, 2 and 3.
5. System module update step that deletes `cache/adminmenu_*.php`; document the page and the store in `docs/symfony-host.md`.

**Rollback**: deleting `<trust>/var/preloads.php` re-enables every preload. Reverting the code leaves a stale menu entry until the menu cache is rebuilt; the store file is then ignored.

## Open Questions

- The icon for the menu and dashboard: `preloads.png`, `_small` and `_big` are copies of the autotasks icons for now.

## Implementation Notes

- **Events (D5).** Four shipped preloads (`autologin`, `bootstrap`, `debug_mode`, `multilanguage`) do not use `event*` methods: they call `icms_Event::attach()` at file scope, sometimes on other namespaces (`icms_core_Session`). The inventory also reads those calls; events of another namespace are shown as `sessionStart (icms_core_Session)`. `googleanalytics.php` declares `IcmsPreloadGa4`, not `IcmsPreloadGoogleanalytics`, so the handler has never attached its events; the page lists it without events. `keepalive` handles `beforeFooter` and `adminHeader`.
- **Store reading (D3, D4).** `App\Preload\DisabledPreloads::names()` reads through `Icms\Preload\Handler::disabledPreloads()`, so the host and the library share one reader. Its tests are therefore in the Integration suite, which has the legacy constants. Paths come from the legacy constants after the `constants` stage (`App\Preload\Preloads`), because the test site's trust path differs from the repository.
- **Menu cache (D2).** `modulesadmin` rewrites the admin menu after every module update, so no cache files are deleted; the update step only raises the system module's database version to 51, so existing sites are offered the update.
- **Template (D7).** The `file:` resource works with the admin renderer; no `db:` fallback was needed. The template is `modules/system/templates/system_adm_preloads.html`, next to the other system templates.
- **Unknown preloads.** The page also lists files whose name cannot be stored (characters outside `[A-Za-z0-9_-]`), without a toggle button.
- **Snapshots.** The admin menu now has a "Preloads" entry, so the characterization snapshots of admin pages (`ipf-admin-list-webmaster`, the mimetype snapshots) were re-recorded; the only difference is that entry.
- **Test run on Windows.** `installTrustDistribution()` sets `set_time_limit(300)`, which on Windows counts wall-clock time and stopped a full run after five minutes. `TrustDistributionTest` resets the limit in `tearDown()`.
- **Tests.** The `php -S` test server supports routing levels 2 and 3; level 1 is covered by requesting `/index.php/admin/preloads`, the URL level 1 serves. Unwritable storage is simulated by a directory at the store's path, which works on Windows too.
