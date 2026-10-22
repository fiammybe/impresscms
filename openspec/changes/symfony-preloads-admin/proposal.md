## Why

Preloads (`plugins/preloads/*.php`) hook into every request, but an administrator cannot see which ones are installed or which events they handle, and the only way to switch one off is to delete or rename its file on the server. A misbehaving preload (a broken analytics snippet, `debug_mode` left on, `autologin` that is not wanted) therefore needs FTP access and leaves the installation modified. The mimetype pilot delivered the building blocks for admin pages served by Symfony (admin context and access, admin page rendering, legacy redirects, token checks). A preloads page is the first admin page that has no legacy counterpart, so it is built on Symfony from the start, under its own URL.

## What Changes

- Add a **preloads administration page** in the system administration, served by a Symfony controller at a new URL (`/admin/preloads`, also reachable as `/index.php/admin/preloads` at routing level 1). It lists every preload file in `plugins/preloads/` with its class, the events it handles and whether it is enabled.
- Administrators can **disable and re-enable** an individual preload. A disabled preload is no longer loaded on any request (legacy scripts and Symfony routes alike) until it is enabled again. The file itself stays untouched.
- The disabled set is stored per site in `<trust>/var/preloads.php`. It is read when the preload handler is created, which happens before the database is available.
- `Icms\Preload\Handler` skips preloads listed as disabled. This is an additive change to the library: without the file, every preload loads as before.
- Register the page as a **system admin function** (`preloads`) with its own category, so it appears in the system admin menu and is protected by the same `system_admin` rights as the other functions. Webmasters always have access; other groups need the right for the new category.
- The system admin menu learns to link a function to a URL other than `admin.php?fct=<dir>`. `admin.php?fct=preloads` redirects to the new URL, so old-style links keep working.
- Enabling and disabling require a valid ImpressCMS security token.

## Capabilities

### New Capabilities
- `preload-activation`: Which preloads are loaded on a request: every preload file is loaded unless the site has disabled it; the disabled set is site configuration outside the web root and survives updates of the preload files.
- `system-preloads-admin`: The preloads administration page: listing installed preloads with their events and state, enabling and disabling them with token protection, access through the system admin rights, and its place in the admin menu.

### Modified Capabilities
<!-- none: the page reuses the admin-page-integration and request-ownership behaviour without changing it -->

## Impact

- **New code (host)**:
  - `src/Controller/System/PreloadController.php`
  - `src/Preload/*` (preload inventory and the disabled-preloads store)
  - a Smarty content template for the page
- **Library (`Icms\`)**: `Icms\Preload\Handler` reads the disabled set and skips those files. Additive and inert without the store file.
- **Legacy files (system module)**:
  - `modules/system/admin/preloads/xoops_version.php` (menu entry and category), with a `main.php` that redirects to the new URL
  - `modules/system/constants.php`: `XOOPS_SYSTEM_PRELOADS`
  - `modules/system/menu.php`: honours a link given by the function
  - language constants in `modules/system/language/*/admin/preloads.php`
- **Dependencies**: builds on the unarchived change `ipf-on-symfony-mimetype-pilot` (`SystemAdminContext`, `AdminPageRenderer`, `LegacyRedirect`). No new packages.
- **Behaviour**: unchanged for every site until an administrator disables a preload. The admin menu cache must be rebuilt for the new entry to appear.
- **Out of scope**:
  - uploading, editing or deleting preload files
  - ordering or prioritising preloads
  - module preloads (`$icmsOnDemandPreload`, currently unused) and library preloads (`Icms\Preload\LibrariesHandler`)
  - Twig rendering of the admin chrome
