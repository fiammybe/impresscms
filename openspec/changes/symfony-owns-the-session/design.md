## Context

**How it works today:**
- `Icms\Core\Session::service()` runs in the `services` boot stage. It installs a database save handler on the `session` table, names and starts the PHP session, and sets `icms::$user` from `$_SESSION['xoopsUserId']`.
- `include/checklogin.php` and the autologin preload log a user in by writing that key.
- The host bridges to this session:
  - `php_bridge` storage;
  - `LegacySessionFactory`, which boots legacy first;
  - `LegacySessionAttributeBag` on `$_SESSION`.
- On every request that asks for a user, the host derives a Symfony user from `icms::$user` (`IcmsSessionAuthenticator`).

**Two ways legacy boots:**
- **Legacy pages** are not booted by the host. The bridge runs the script, and the script boots ImpressCMS itself (interactive mode).
- **Symfony routes** boot legacy headless through `LegacyRuntime`, and only when they need it.

## Goals / Non-Goals

**Goals:**
- One session, started and stored by Symfony, for legacy pages and Symfony routes.
- The Symfony security token decides who is logged in; legacy reads it.
- The existing legacy login (password, LDAP, autologin) keeps working, but authenticates through Symfony.
- Live sessions survive the upgrade.

**Non-Goals:**
- Replacing the legacy password check or the autologin cookie. That is `symfony-password-login` and `remember-me-and-email-links`.
- Changing what legacy code finds in `$_SESSION`, apart from the identity keys becoming a mirror.

## Decisions

### D1. Keep the `session` table, with a DBAL handler

`App\Session\IcmsDatabaseSessionHandler` is an `AbstractSessionHandler`. It reads and writes `sess_id`, `sess_updated`, `sess_ip` and `sess_data` the way `Icms\Core\Session` did, including the IP binding: A.B.C for IPv4, seven groups for IPv6.

Both use PHP's session serialization, so existing sessions remain valid. There is no locking, as before.

*Alternative:* Symfony's `PdoSessionHandler` on a new table. It would log every user out once. Its transactional locking would also wrap whole requests on the connection that legacy code shares.

### D2. Session settings without booting legacy

`App\Session\SessionSettings` reads `use_mysession`, `session_name` and `session_expire` from the `config` table with one DBAL query. The table prefix comes from `mainfile.php` (`LegacyRuntime::bootTo('constants')`).

`App\Session\IcmsSessionStorageFactory` builds a `NativeSessionStorage`:
- **name**: `session_name` with `use_mysession`, otherwise `ICMSSESSION`;
- **cookie lifetime and `gc_maxlifetime`**: `session_expire` minutes with `use_mysession`, otherwise a browser session;
- **cookie flags**: `cookie_httponly`, `cookie_samesite=lax`, and `cookie_secure` for HTTPS requests;
- **cookie path**: the request's base path.

So `GET /up` still touches nothing, and a Symfony route can start the session before any legacy code runs.

### D3. A seam in the legacy library: `Icms\Core\SessionHost`

The legacy library defines the interface `SessionHost`, with four methods: `start()`, `userId()`, `login(int)` and `logout()`. The host implements it as `App\Security\LegacySessionHost` and installs it with `Icms\Core\Session::setHost()` on every main request (kernel.request).

Without an installed host, `Icms\Core\Session` keeps its own handler and behaviour. Command-line scripts and isolated legacy tests are therefore unaffected.

Legacy pages run after `HttpKernel::handle()` has returned, when the request has left the request stack. `Security::login()` needs a current request, so the host keeps the request it was installed for and pushes it back on the stack.

### D4. The token is the identity; the session keys are a mirror

With a host, `Icms\Core\Session::service()`:
1. starts the host's session;
2. reads the user id from the token. `TokenStorageInterface::getToken()` lets the lazy firewall load and refresh it;
3. fires `sessionStart` for preloads;
4. if a preload set `$_SESSION['xoopsUserId']` for an anonymous visitor, logs that user in through the host. The autologin preload therefore works unchanged;
5. loads `icms::$user`, its groups from the database and `UserLanguage`, and writes `xoopsUserId` and `xoopsUserGroups` into `$_SESSION`.

Writing the mirror keys anywhere else has no effect.

**One exception: logins from before the upgrade.** These sessions were stored by `Icms\Core\Session` itself. Their data has no Symfony metadata (`_sf2_meta`), and `IcmsDatabaseSessionHandler` notices this when it reads them. For such a session, `LegacySessionHost::userId()` logs in the user of `xoopsUserId` once. Symfony stamps every session it stores, so from then on the keys are only a mirror. That way an upgrade does not log anyone out.

**`IcmsUserProvider`** loads `IcmsUser` with DBAL from `users` and `groups_users_link`. Users with `level < 1` and deleted users are not found, so their token is dropped.

**`IcmsUser`** implements `EquatableInterface` on the id. A group change therefore does not log the user out. That matches the old behaviour: groups were taken at login, and from the database on each page.

### D5. Legacy login and logout through the host

**Login.** `Icms\Core\Session::login($user)` replaces this sequence in `checklogin.php`:
```php
session_regenerate_id(true);
$_SESSION = [];
$_SESSION['xoopsUserId'] = …;
```
With a host it clears `$_SESSION` and calls `SessionHost::login()`, which runs `Security::login()` with `App\Security\LegacyLoginAuthenticator`.
- **The authenticator** is never triggered by a request. It only gives programmatic login a firewall authenticator, and it is the firewall's entry point (redirect to `/user.php?xoops_redirect=`).
- **Symfony** migrates the session id on login and stores the token at kernel.response. The host dispatches kernel.response for legacy responses too, also after `exit()`.

**Logout.** `Icms\Core\Session::sessionClose()` clears the token and invalidates the session.

### D6. What is dropped

- **`sslpost_name`**: the session id carried in a POST field from an HTTP to an HTTPS page. Sites run over HTTPS throughout.
- **The per-request `removeExpiredCustomSession()` check**: the cookie and the stored session expire together.

## Risks / Trade-offs

- **Two database connections for legacy pages.** Interactive legacy pages open their own PDO, and the session handler uses DBAL's. The session handler does not take part in legacy transactions, as before.
- **Third-party code that logs users in by writing `xoopsUserId` outside `sessionStart`** no longer logs them in. Core does not do this; the autologin preload is covered by D4.
- **Symfony's session listener** adds `Cache-Control: private` to responses that used the session. Legacy pages already sent no-cache headers when logged in.
