## 1. Disabled preloads in the library (D4)

- [x] 1.1 Add `Handler::disabledPreloads()` (reads `ICMS_TRUST_PATH/var/preloads.php`, returns `[]` for a missing path, missing file or unexpected shape, never throws) and skip listed names in the constructor before `include_once`
- [x] 1.2 Tests (spec `preload-activation`): no file loads every preload; a disabled name is not included and its events do not fire; an unknown name is ignored; a corrupt file loads every preload. Tag them with `#[Group('spec:preload-activation')]` and their `@scenario`
- [x] 1.3 HTTP test: with `keepalive` disabled, neither a legacy script nor a Symfony route that boots legacy attaches its event

## 2. Store and inventory in the host (D3, D5)

- [x] 2.1 Implement `App\Preload\DisabledPreloads`: read, `disable()`/`enable()` with name validation, atomic write (temporary file plus `rename()`, `flock()` on a lock file, `opcache_invalidate()`), an exception naming `<trust>/var` when it cannot write
- [x] 2.2 Implement `App\Preload\PreloadInfo` and `App\Preload\PreloadInventory::all()`: the file list of the handler, class name, enabled state, events parsed with `token_get_all()` without including the file
- [x] 2.3 Tests (Integration suite: the store reads through the library class, which needs the legacy constants): store round trip, invalid and path-traversal names are rejected, read-only directory throws and keeps the old file; inventory events equal the events the handler attaches, for every shipped preload
- [x] 2.4 Register both as services in `config/services.yaml` with the trust path and `ICMS_PRELOAD_PATH` as arguments

## 3. Registration as a system admin function (D2)

- [x] 3.1 Add `XOOPS_SYSTEM_PRELOADS` (25) to `modules/system/constants.php`
- [x] 3.2 Add `modules/system/admin/preloads/xoops_version.php` (category, group `_MD_AM_GROUPS_SYSTEMTOOLS`, `link`), the icons, and `main.php` that redirects `admin.php?fct=preloads` to `ICMS_URL/index.php/admin/preloads`
- [x] 3.3 Make `modules/system/menu.php` use the `link` key when present
- [x] 3.4 Add `_MD_AM_PRELOADS`, `_MD_AM_PRELOADS_DSC` and the page strings in `language/<lang>/admin/preloads.php` and the system admin language file, for every language, so the language consistency test passes
- [x] 3.5 Add a system module update step with the dbversion bump (51), so existing sites are offered the update; modulesadmin rewrites the admin menu after every module update, so no cache files need deleting
- [x] 3.6 Tests: the menu entry links to the page for a webmaster and is absent for an administrator without the right; the group administration page offers the preloads right

## 4. Controller and page (D1, D6, D7)

- [x] 4.1 Verify that `AdminPageRenderer` can render a `file:` Smarty template; otherwise implement the `db:` fallback of the design's risks
- [x] 4.2 Add the content template `modules/system/templates/admin/preloads/system_adm_preloads.html` (table with name, events, state, toggle form with token; security warning for `protector`), indented with 4 spaces
- [x] 4.3 Implement `App\Controller\System\PreloadController` with `#[SystemAdminFunction('preloads')]`: `GET /admin/preloads` (list) and `POST /admin/preloads/toggle` (token check, name and state validation, store update, `LegacyRedirect` with the messages of D6)
- [x] 4.4 HTTP tests (spec `system-preloads-admin`), tagged with `#[Group('spec:system-preloads-admin')]`:
  - the page at `/admin/preloads` (level 2) and `/index.php/admin/preloads` (level 1); the redirect from `admin.php?fct=preloads`
  - access: webmaster, anonymous, `mediaadmin` without the right, a group granted the right
  - the list on the fixture site, and a disabled preload listed with its events and without running
  - disable and enable with a token, each followed by a request that does (not) load the preload
  - unknown and traversal names, missing token, GET on the toggle route, read-only storage: nothing changes
- [x] 4.5 Check that `<trust>/var/preloads.php` is not reachable over HTTP at any routing level

## 5. Documentation and verification

- [x] 5.1 Document the page, the store file and its recovery (delete the file) in `docs/symfony-host.md`, next to "Taking over an IPF admin page"
- [x] 5.2 Run `bin/spec-coverage` with no gaps for `preload-activation` and `system-preloads-admin`
- [x] 5.3 Run all suites on both document roots and at routing levels 1, 2 and 3; run PHPStan on `src/` with no new errors
