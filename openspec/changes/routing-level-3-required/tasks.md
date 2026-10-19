## 1. Routing detection and refusal (D1-D3, D5)

- [x] 1.1 `htdocs/index.php` defines `ICMS_ROUTED_BY_INDEX` before running a requested script without a host. `icms_host_routed()` in `htdocs/include/host.php` checks it and `ICMS_HOST_AUTOLOADED`
- [x] 1.2 `icms_host_routing_page()` / `icms_host_require_routing()`: 503 with `htdocs/routing.html` for a script the web server ran itself while the host is installed. Not for the command line, and not before the host is installed. Called in `htdocs/include/common.php` after the PHP version check
- [x] 1.3 `htdocs/routing.html`: a self-contained page with the configuration for Apache/LiteSpeed, nginx and FrankenPHP/Caddy

## 2. Installing the host only on a routing server (D1, D4)

- [x] 2.1 `icms_host_installable()` (routed, or the command line), `icms_host_write_htaccess()` (Apache and LiteSpeed only; never overwrites) and `icms_host_routing_advice()`
- [x] 2.2 `htdocs/install/page_movevendor.php` refuses to install the host when the request was not routed; string `HOST_ROUTING_REQUIRED`
- [x] 2.3 System update step 50 refuses to install the host when the request was not routed. The update stops below dbversion 50 and the staging directory is kept

## 3. Server configurations

- [x] 3.1 `htdocs/htaccess.txt` with the level 3 rule active
- [x] 3.2 `docs/server/nginx.conf`, `Caddyfile` and `apache-vhost.conf` at level 3 only. The Herd driver loses `ICMS_HERD_ROUTING`

## 4. Tests and CI

- [x] 4.1 `ServerProcess`: `ICMS_TEST_ROUTING` removed, every request routed by default, `notRouted()` for a server that runs scripts itself
- [x] 4.2 `HostDeploymentTest`: the refusal on a server without routing, and scripts running before the host is installed. Drop the level conditions in `RequestOwnershipTest`, `LegacyAssetsTest` and `LegacyFixtureTestCase`
- [x] 4.3 `HostUpgradeTest`: the upgrade runs on a routing server; a new test shows that a server without routing does not get the host
- [x] 4.4 Unit tests for `icms_host_routing_page()`, `icms_host_write_htaccess()` and `icms_host_routing_advice()`
- [x] 4.5 CI: the Http suite runs once (level 3). The `webservers` matrix has no level dimension; `.github/scripts/webserver.sh` uses the configurations as they are

## 5. Docs

- [x] 5.1 `docs/symfony-host.md` (routing section, Herd, tests) and `README.md` (installation)
- [x] 5.2 Verify on the Herd test site with the ImpressCMS driver, and on a server without routing
  - Done: `pre3.test` with the new driver: `/`, `/user.php`, `/up`, login and the admin work; `/trust_dist/` and `/templates_c/` give 404. A server without routing is covered by `HostDeploymentTest` and `HostUpgradeTest` (`php -S` without router)
