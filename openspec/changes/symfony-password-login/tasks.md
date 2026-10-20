## 1. Password formats and hashing (D1-D3)

- [x] 1.1 Record test vectors of every legacy format with the unchanged 2.x code: `tests/Fixtures/passwords/legacy-vectors.json` (enc_type 0-16, 20, 21-40)
- [x] 1.2 `Icms\Core\Password\Legacy`: `describe()`, `digest()`, `wrap()`, `verify()`; library test `htdocs/tests/Unit/Core/Password/LegacyTest.php` with every vector, plain and wrapped
- [x] 1.3 `Icms\Core\Password`: `algorithm()` (Argon2id, else bcrypt), `encryptPass()` with `hasher()`, `matches()`, `needsRehash()`; `verifyPass()` for every format. `Icms\Member\Handler::loginUser()` verifies, then loads by login name, and refreshes an outdated hash
- [x] 1.4 Host: `App\Security\Password\Hasher\Modern` (the shared hasher) for `App\Security\User\Entity`, with `App\Security\Password\Hasher\Legacy` as `migrate_from`

## 2. Migration (D4)

- [x] 2.1 `Icms\Core\Password\Wrapper` (any PDO): `pending()` and `wrap()` with a time budget
- [x] 2.2 System update step 51 (`ICMS_SYSTEM_DBVERSION` 51): removes the `enc_type` preference, then wraps for up to 20 seconds; stays below 51 while hashes are left
- [x] 2.3 `bin/console icms:passwords:wrap` (`App\Command\Password\Wrap`)
- [x] 2.4 Admin dashboard warning `_PASSWORDS_UNWRAPPED` (all five `global.php`)

## 3. Login (D5, D6)

- [x] 3.1 `App\Security\User\Entity`: login name as the identifier, the self-describing password (not serialized), level and expiry. `App\Security\User\Provider`: login name or e-mail, `PasswordUpgraderInterface`
- [x] 3.2 `App\Security\User\Checker`: not activated, closed site, multiple logins (`App\Legacy\Dbal\Config` reads the preferences)
- [x] 3.3 `App\Security\Authenticator\Password`: the legacy and closed-site login forms, same-site check, `ldap`/`ads` through the legacy classes, legacy redirects and messages, expired passwords to the reset form; `Icms\Core\Session::loggedIn()` for last login, theme and notifications
- [x] 3.4 `login_throttling` (5 a minute) with `symfony/rate-limiter`; `_US_LOGINTHROTTLED`
- [x] 3.5 `include/checklogin.php` only for login posts that reach legacy (no host, or an older one, until the update), without the 32-character cut
- [x] 3.6 Session cache headers: no header from the session handler (`cache_limiter` `0`); legacy pages get PHP's former "nocache" headers from `App\Session\Host` (`HostRunner::LEGACY_PAGE`)

## 4. Cleanup

- [x] 4.1 `enc_type` removed from `install/makedata.php` and the forced expiry from the preferences; LDAP provisioning stores `$icms-external$` instead of an MD5 hash

## 5. Tests and docs

- [x] 5.1 `tests/Http/PasswordLoginTest.php`: return URL, wrong password, e-mail, cross-site, expired, long password, upgraded and unwrapped legacy hashes, not activated, closed site, throttling
- [x] 5.2 `HostUpgradeTest`: a 2.0 hash is wrapped by the update and logs in afterwards; the login before the host is installed uses `checklogin.php`
- [x] 5.3 Characterization snapshots of the three login posts re-recorded: the host's redirect (302 with `Location`), and the remember-me post no longer ends in the `ICMS_DB_PASS` error
- [x] 5.4 `docs/symfony-host.md` (*Session and identity*)
- [x] 5.5 Verify on the Herd test site: an upgrade wraps the hashes, and login, wrong password and throttling work
  - Done on `pre3.test`, upgraded as a site without a host:
    - the login before the update ran through `checklogin.php` and replaced the legacy `$23$` hash at once;
    - the update installed the host and reached dbversion 51;
    - through the new host, a wrong password shows "Incorrect Login!", and the right one logs in and opens the admin;
    - a bcrypt hash became Argon2id at the next login.
  - Throttling is covered by `PasswordLoginTest`.
  - Symfony's `auto` turned out to choose bcrypt, hence `Icms\Core\Password::algorithm()`.
