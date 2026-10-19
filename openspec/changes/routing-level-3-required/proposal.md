## Why

ImpressCMS 3 moves the session and every login method into the Symfony host. That work is planned as the changes `symfony-owns-the-session`, `symfony-password-login`, `remember-me-and-email-links`, `passkeys` and `external-identities`.

The host can only own the session if it sees every request. Today a site works at three routing levels:
- level 1 needs no server configuration;
- level 2 adds a fallback for unknown paths;
- level 3 routes every PHP request.

At levels 1 and 2 the web server runs legacy scripts such as `/user.php` itself, without the host. Those requests would have no session and no identity once the host owns them.

## What Changes

- **Routing level 3 is required.** Every PHP request must reach the web root's `index.php`. Levels 1 and 2 are no longer supported.
  - `htdocs/htaccess.txt` has the level 3 rule active.
  - `docs/server/` (nginx, FrankenPHP/Caddy, Apache) and the Laravel Herd driver have only the level 3 configuration.
  - `ICMS_HERD_ROUTING` is removed.
- **Scripts the web server runs itself are refused once the host is installed.**
  - `include/common.php` answers 503 with the new self-contained page `htdocs/routing.html`, which explains the configuration per web server.
  - Command-line scripts are not affected.
  - Before the host is installed (the installer, or an upgrade before the system module update), scripts still run.
- **The host is installed only on a server that routes every PHP request.**
  - The installer's host step and the system module update check that their own request was routed through `index.php`.
  - If it was not, they do not install the host and explain what to configure.
  - On Apache and LiteSpeed they first write `.htaccess` from `htaccess.txt` when the web root has none. An existing `.htaccess` is never changed.
- **Tests and CI run at level 3 only.**
  - `ICMS_TEST_ROUTING` is removed.
  - A test server without routing (`ServerProcess::notRouted()`) checks the refusal.
  - The `webservers` job runs Apache, nginx and FrankenPHP with the level 3 configuration.

## Capabilities

### New Capabilities
None.

### Modified Capabilities
- `host-deployment`: the host requires routing level 3. It refuses scripts the web server runs itself, and it is installed only on a routing server.
- `request-ownership`: the single entry point receives every PHP request. Directly executed legacy scripts are refused instead of sharing the session.

## Impact

- **Sites on servers without rewriting** must configure it.
  - On Apache with `.htaccess` support, the installer or the update does it for them.
  - Shared hosting without `mod_rewrite` is no longer supported.
- **Upgrades:**
  - A 2.0.x site keeps working until the system module update. The update installs the host only once routing works.
  - A 3.0.0 Alpha 1 site that already has the host and no level 3 configuration shows `routing.html` until the server is configured.
- **Code:**
  - `htdocs/include/host.php`: new functions `icms_host_routed()`, `icms_host_routing_page()`, `icms_host_require_routing()`, `icms_host_write_htaccess()` and `icms_host_routing_advice()`.
  - Also changed: `htdocs/index.php`, `htdocs/include/common.php`, `htdocs/install/page_movevendor.php` and update step 50 in `htdocs/modules/system/include/update.php`.
