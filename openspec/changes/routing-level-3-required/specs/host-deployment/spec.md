## REMOVED Requirements

### Requirement: The host works at every routing level the web server provides
**Reason**: The host owns the session and the identity from ImpressCMS 3 on, so legacy scripts can no longer run beside it.
**Migration**: Configure the web server to send every PHP request to `index.php`: rename `htdocs/htaccess.txt` to `.htaccess` on Apache, or use the configurations in `docs/server/`.

## ADDED Requirements

### Requirement: Every PHP request reaches the host
The system SHALL require the web server to send every PHP request, and every request for an unknown path, to the web root's `index.php` (routing level 3). The host SHALL handle those requests with responses identical to direct execution of the legacy scripts. Once the host is installed, a legacy script that the web server executes itself SHALL NOT run. The response SHALL be `routing.html` from the web root with status 503. Command-line scripts SHALL NOT be affected. Before the host is installed, legacy scripts SHALL still run, so that the installer and an upgrade can reach the system module update.

#### Scenario: Symfony route through index.php
- **WHEN** `GET /up` arrives
- **THEN** the host responds with status 200 and body `ok`

#### Scenario: Legacy script through the host
- **WHEN** `GET /user.php` arrives
- **THEN** the host executes `user.php` through the bridge and the response matches the characterization snapshot

#### Scenario: Script executed by the web server itself
- **WHEN** the host is installed and the web server executes `/user.php` itself instead of sending it to `index.php`
- **THEN** the response is 503 with the routing page and `user.php` does not run, while `/` still reaches the host, and before the host is installed `user.php` runs

#### Scenario: Supported web servers
- **WHEN** the example configuration from `docs/server/` is used on Apache, nginx or FrankenPHP
- **THEN** the HTTP suite passes

### Requirement: The host is installed only on a routing server
The installer and the system module update SHALL install the host only when their own request reached PHP through the web root's `index.php`, or when the update runs on the command line. Otherwise they SHALL leave the trust path and the staging directory unchanged and explain how to configure the web server. On Apache and LiteSpeed they SHALL first write `.htaccess` from `htaccess.txt` when the web root has no `.htaccess`. They SHALL NOT change an existing `.htaccess`.

#### Scenario: The host is installed only on a routing server
- **WHEN** the system module update of a site without a host runs on a web server that executes the update script itself
- **THEN** the host is not installed, the staging directory is kept, the system module stays below dbversion 50, and the page explains how to route every PHP request to `index.php`
