## 1. Session storage (D1, D2)

- [x] 1.1 `App\Session\IcmsDatabaseSessionHandler`: an `AbstractSessionHandler` on the `session` table, with the IP binding of `Icms\Core\Session`
  - A session that was never stored and has no cookie is not invalidated with a cookie
- [x] 1.2 `App\Session\SessionSettings`: `use_mysession`, `session_name` and `session_expire` with one DBAL query; `App\Legacy\Dbal\LegacyTables` for the table prefix
- [x] 1.3 `App\Session\IcmsSessionStorageFactory`: `NativeSessionStorage` with the name and lifetime from the preferences
  - `HttpOnly`, `SameSite=Lax`, `secure` for HTTPS, `cache_limiter=nocache` as before
  - `framework.session.storage_factory_id`
- [x] 1.4 `App\Legacy\LegacySessionFactory` no longer boots legacy
  - Services take the DBAL connection as a closure: building the connection reads `mainfile.php`, which must not happen before a legacy page includes it

## 2. Identity (D3, D4)

- [x] 2.1 `Icms\Core\SessionHost` in the legacy library; `Icms\Core\Session::setHost()`, `host()` and `hostedService()`
  - The token decides; the session keys are a mirror; a preload that sets `xoopsUserId` while the session starts logs that user in; a session stored before the host (no `_sf2_meta`) is taken over once
  - `XOOPS_DB_SALT` is read in the constructor, because `setHost()` runs before `mainfile.php`
- [x] 2.2 `App\Security\LegacySessionHost`: installed on every main request
  - `start()`, `userId()`, `login()` with `Security::login()`, and `logout()`
  - PHP's own session cookie is removed: Symfony's session listener sends it when needed
- [x] 2.3 `App\Security\IcmsUserProvider` with DBAL (`users`, `groups_users_link`, `level > 0`); `IcmsUser` implements `EquatableInterface` on the id
- [x] 2.4 Remove `IcmsSessionAuthenticator`. `App\Security\LegacyLoginAuthenticator` is used for programmatic login and is the entry point

## 3. Legacy login and logout (D5)

- [x] 3.1 `Icms\Core\Session::login()`; `include/checklogin.php` uses it. `sessionClose()` logs out through the host

## 4. Tests and docs

- [x] 4.1 New `tests/Http/SessionOwnershipTest.php`:
  - storage in the `session` table;
  - a new session id at login;
  - the session keys as a mirror;
  - a deactivated user;
  - login by a preload.
  `tests/Unit/Session/IcmsDatabaseSessionHandlerTest.php` covers the IP binding
- [x] 4.2 Characterization snapshots re-recorded. The only differences are:
  - the session cookie is `HttpOnly; SameSite=Lax`;
  - one cookie instead of two with a custom session name;
  - Symfony's cache directives (`max-age=0, must-revalidate, private`) are added;
  - a line number in the existing remember-me error (`ICMS_DB_PASS`, fixed by `remember-me-and-email-links`)
- [x] 4.3 `IdentityTestController` boots legacy for `icms::$user`. `KernelBootTest` allows `Icms\Core\SessionHost`
- [x] 4.4 `docs/symfony-host.md`, section *Session and identity*
- [x] 4.5 Verify on the Herd test site: an existing session survives, plus login, logout and the admin
  - Done on `pre3.test`: a webmaster logged in before the deploy is still logged in afterwards (taken over once); logout, a new login and the admin pages work; `/`, `/up` and `/user.php` give 200
