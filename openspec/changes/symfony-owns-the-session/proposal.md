## Why

ImpressCMS 3 replaces the legacy login code (salted hashes, a hash-derived remember-me cookie, passwords mailed in plain text) with Symfony's security components and adds passkeys, login links and external identities. Those components authenticate a Symfony security token and keep it in a session that Symfony controls. Today the opposite holds: ImpressCMS starts and stores the session (`Icms\Core\Session`, design D10 of `migrate-to-symfony-foundation`) and the host only reads the legacy user from it on every request (D11).

`routing-level-3-required` made every PHP request reach the host, so the host can now own the session and the identity for the whole site.

## What Changes

- **The host owns the session.** Symfony starts it with native PHP sessions and stores it through a new DBAL session handler on the existing `session` table, with the IP binding of the legacy handler. Name, lifetime and cookie come from the ImpressCMS preferences (`use_mysession`, `session_name`, `session_expire`), read with one DBAL query, so the host needs no legacy boot to start a session. Existing sessions stay valid.
- **The Symfony security token is the identity.** `User\Provider` loads users and their groups with DBAL; a deleted or deactivated user is anonymous on the next request. `IcmsSessionAuthenticator` and the legacy boot it did on every request are removed.
- **Legacy uses the host's session through a seam.** The legacy library gets `Icms\Core\Session\IHost`; the host installs its implementation for every request. With a host, `Icms\Core\Session::service()` starts the host's session, sets `icms::$user` from the token and writes `$_SESSION['xoopsUserId']` and `xoopsUserGroups` as a read-only mirror for modules. Without a host (command line, isolated tests) it works as before.
- **Legacy login and logout go through the host.** `include/checklogin.php` and the logout in `user.php` call new `Icms\Core\Session::login()` and `sessionClose()`, which log in through Symfony's `Security::login()` (session id regenerated) and invalidate the session. A preload that sets `xoopsUserId` while the session starts (the autologin preload) still logs the user in.
- **Removed:** the per-request legacy session save handler under the host, the `sslpost_name` session id transfer, and `LegacySessionFactory`'s legacy boot.

## Capabilities

### New Capabilities
None.

### Modified Capabilities
- `session-identity`: the session and the identity are owned by the host; legacy login and logout use it.

## Impact

- Sessions keep working across the upgrade (same table, same serialization); no schema change, no dbversion step.
- Code: `config/packages/framework.yaml`, `config/packages/security.yaml`, `src/Security/*`, new `src/Session/*`, `src/Legacy/LegacySessionFactory.php`, new `htdocs/libraries/Icms/Core/Session/IHost.php`, `htdocs/libraries/Icms/Core/Session.php`, `htdocs/include/checklogin.php`, `htdocs/user.php`.
- Modules that read `$_SESSION['xoopsUserId']` keep working; modules that write it to log a user in only work while the session starts (as the autologin preload does).
