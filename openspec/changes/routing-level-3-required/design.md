## Context

`relocate-host-into-trust-path` (archived 2026-10-14) made the host work without any server configuration. It defined three routing levels and supported all of them on Apache, nginx, FrankenPHP and Laravel Herd.

The authentication plan for ImpressCMS 3 has Symfony own the session, its storage and the identity. This supersedes D10 and D11 of `migrate-to-symfony-foundation`. A legacy script that the web server runs itself would bypass that session, so every PHP request has to go through the host.

## Goals / Non-Goals

**Goals:**
- Every PHP request of an installed site runs through the host.
- A misconfigured server shows a clear page instead of an inconsistent site.
- The installer and the upgrade never activate the host on a server that cannot route to it.
- Apache sites with `.htaccess` support are configured automatically.

**Non-Goals:**
- Supporting hosts without any rewriting.
- Moving the session to Symfony. That is `symfony-owns-the-session`.

## Decisions

### D1. Detect routing from the request itself, not with a probe

`index.php` defines `ICMS_ROUTED_BY_INDEX` before it runs a requested script on a site without a host. The host defines `ICMS_HOST_AUTOLOADED`. `icms_host_routed()` is true when either is defined.

The installer's host step and the system module update are themselves PHP scripts other than `index.php`. So their own request proves whether the server routes every PHP request.

On the command line (`bin/test-db`, scripted upgrades) no web server is involved. `icms_host_installable()` therefore also accepts `cli` and `phpdbg`.

*Alternatives:*
- A loopback HTTP request from PHP. Shared hosting often blocks it, and it needs the site's public URL.
- A JavaScript probe in the browser. This needs an extra round trip and a second code path.

### D2. Refuse in `include/common.php`, not in every script

Every legacy page includes `mainfile.php`, which includes `include/common.php`. The refusal sits right after the PHP version check, before anything starts a session.

Scripts that do not include `mainfile.php` are not ImpressCMS pages; some are static helpers, some are protected by their own `.htaccess`.

Command-line scripts (`cli`, `phpdbg`) are exempt, because cron jobs and `bin/console` never pass through a web server.

### D3. Only refuse once the host is installed

Before the host is installed, there is no session in Symfony to protect. The installer and a 2.0.x site that has just been uploaded must run on any server. The system module update then refuses to install the host until routing works (D1). So a site cannot get stuck between the two states.

### D4. Write `.htaccess` only when there is none

On Apache and LiteSpeed (detected from `SERVER_SOFTWARE`), the installer and the update copy `htaccess.txt` to `.htaccess` if the web root has none. They then ask the admin to reload. A custom `.htaccess` is never changed, because it may hold rules of the site. The admin gets the block to add instead.

### D5. 503 and a self-contained page

Like `requirements.html`, `routing.html` uses no theme, script or image of the site. The site cannot run at this point. Status 503 with `Retry-After` tells search engines that the site comes back.

## Risks / Trade-offs

- **Hosts without rewriting lose support.** This is accepted for 3.0. `routing.html` tells them why.
- **A 3.0.0 Alpha 1 site at level 1 or 2 is refused right after uploading the new files.** Its admin cannot run the update until the server is configured. `routing.html` explains how. Alpha 1 is a test release.
- **Scripts run before `include/common.php`.** Code that a script runs before it includes `mainfile.php` still executes on a misconfigured server. Legacy scripts only set variables there.
