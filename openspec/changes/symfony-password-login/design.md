## Context

**The legacy formats that `Icms\Core\Password` produces** (`tests/Fixtures/passwords/legacy-vectors.json` records them):

| Format | Value |
|---|---|
| Stored | `$<enc>$<iterations>$<salt>-<hash>`, with `enc` 21-40. `hash` is the `enc` algorithm over `sha256(salt.salt) . sha256(pass.pass) . sha256(main.main)`, where `main` is `XOOPS_DB_SALT`. |
| `$20$` | `$20$20$<md5(pass)>` |
| Salt column | `<hash>`, where the `salt` and `enc_type` columns give `algo(enc_type)(salt . md5(pass) . main)`; `enc_type` 0 is `md5(pass)` |

**How the login works today.** The login goes through `include/checklogin.php`, `Icms\Auth\Factory` (xoops/ldap/ads) and `Icms\Member\Handler::loginUser()`. That last method verifies the password and then looks the user up by `pass` = the returned hash.

## Goals / Non-Goals

**Goals:**
- No legacy hash remains in the database after the upgrade.
- Every legacy format still logs in.
- The login gets throttling and constant-time verification, through Symfony's security layer.
- Themes, blocks and modules that post the legacy login form keep working.

**Non-Goals:**
- Remember-me, login links and password reset by link (`remember-me-and-email-links`).
- Passkeys and external identities.

## Decisions

### D1. The legacy algorithms move to `Icms\Core\Password\Legacy`

The class has static methods without state. `XOOPS_DB_SALT` is passed in or read when called, never as a property default, because the host loads the class before `mainfile.php`.

- `describe($pass, $saltColumn, $encTypeColumn)` turns a stored value into a self-describing one. A salt-column hash becomes `$icms-column$<enc_type>$<salt>$<hash>`.
- `wrap($described, $hasher)` and `verify($stored, $plain)` handle the other formats.

`Icms\Core\Password` keeps its public API for modules:
- `encryptPass()` returns a hash of `hasher()` (D3).
- `verifyPass()` handles Argon2id/bcrypt, wrapped and legacy hashes. It returns the stored value on success, as before.

### D2. The wrap format

```
$icms-legacy$<scheme>.<enc>.<iterations>.<salt>$<argon2id(digest)>
```

- `scheme` is `s` (stored), `c` (salt column) or `m` (`$20$`).
- `digest` is exactly the legacy value. To verify, the digest is recomputed from the password and checked against the Argon2id part.
- Salts are alphanumeric, so `.` and `$` are safe separators. The result is shorter than the 255 characters of `pass`.
- After wrapping, the `salt` and `enc_type` columns are no longer read. They are removed in `login-methods-ui-and-cleanup`.

*Alternative:* rehash on login only. Users who stay away would keep weak hashes. The user chose wrapping.

### D3. One hasher, in both applications

`Icms\Core\Password::hasher()` builds it with Symfony's `PasswordHasherFactory` for `Icms\Core\Password::algorithm()`: `argon2id` where PHP offers it (libsodium or `PASSWORD_ARGON2ID`), otherwise `bcrypt`. Symfony's `auto` is not used, because it chooses bcrypt.

The host configures:
```yaml
password_hashers:
    App\Security\User\Entity:
        id: App\Security\Password\Hasher\Modern
        migrate_from: [icms_legacy]
```
- `Modern` delegates to `Icms\Core\Password::hasher()`. Both applications therefore produce the same hashes, and `needsRehash()` does not churn.
- `icms_legacy` is `App\Security\Password\Hasher\Legacy`, which delegates to `Icms\Core\Password\Legacy`.
- A bcrypt hash on a server that gains Argon2 is rehashed at the next login.

**Rehash on login.** `App\Security\User\Provider` implements `PasswordUpgraderInterface`. `PasswordMigratingListener` stores a plain hash of that algorithm after a successful login with a wrapped or legacy hash.

### D4. Migration in batches, and a command

**Step 51** of the system module update wraps the legacy hashes: values without `$` (salt-column format) and values that start with `$<digits>$`. Modern hashes, wrapped values and markers start with `$` and a letter.
- It works in batches of 50, until 20 seconds have passed. Argon2id costs about 50-100 ms per hash.
- If hashes are left, it says so and keeps dbversion 50. Running the update again continues.
- `bin/console icms:passwords:wrap` (`App\Command\Password\Wrap`) wraps everything without a time limit.
- The admin dashboard shows a warning while unwrapped hashes are left.

### D5. `App\Security\Authenticator\Password`

**When it applies:** a POST to `/user.php` with `op=login`, or any POST with `xoops_login` (the closed-site form). Fields: `uname`, `pass` and `xoops_redirect`. The firewall is lazy, but supports-true authenticators run eagerly. No route is needed.

**Credentials:**
- If the ImpressCMS auth method (`Icms\Auth\Factory::getAuthConnection()`, which honours `ldap_users_bypass`) is `xoops`: `PasswordCredentials`, so the hashers.
- Otherwise: `CustomCredentials`, which calls the legacy `ldap`/`ads` class. Provisioning creates or updates the user before it is loaded.

**User lookup:** `User\Provider::loadUserByIdentifier()` takes `login_name`, or the e-mail address when the input contains `@`, as `loginUser()` does. `User\Entity::getUserIdentifier()` becomes `login_name`.

**Cross-site posts** are rejected when `Origin`, or else `Referer`, names another host. This replaces a token, which legacy forms do not carry.

**Response:** the legacy redirect with a message. `$_SESSION['redirect_message']` and a 302 to the validated `xoops_redirect` (same host, never `register`), or to `/user.php`.
- On success, the message is `_US_LOGGINGU`.
- With `pass_expired`, the redirect goes to `user.php?op=resetpass` with `_US_PASSEXPIRED`.
- On failure, the message is `_US_INCORRECTLOGIN`.
- The messages come from the legacy language files (headless boot to `language`).

**Until the update installs this host** (an upgrade: no host yet, or the older one of a previous 3.0 release), `user.php` and the closed-site page include `include/checklogin.php` for a login post, with the same `Icms\Core\Password` verification. The new host never lets a login post reach them.

**After login,** `Icms\Core\Session::loggedIn()` does what `checklogin.php` did after the session:
- `last_login`;
- `xoopsUserLastLogin` and `xoopsUserTheme`;
- the notification maintenance.

### D6. Throttling and checks

**Throttling:** `login_throttling: { max_attempts: 5, interval: '1 minute' }`, from Symfony's limiter keyed on user name and IP. Protector's brute-force check still runs for legacy posts.

**`App\Security\User\Checker`:**
- **pre-auth:** `level` 0 is not activated (`_US_NOACTTPADM`), `level` -1 is deactivated.
- **post-auth:** closed site (`closesite`, `closesite_okgrp`, the webmasters group); `multi_login`, which checks the online table as `checklogin.php` did.

These rules read the configuration with DBAL.

## Risks / Trade-offs

- **Argon2id cost on shared hosting.** About 64 MB and 50-100 ms per login and per wrapped hash. This is acceptable; the batches keep the update within PHP time limits.
- **Downgrade is impossible after step 51.** The upgrade notes require a backup.
- **LDAP/ADS cannot be tested in CI.** The legacy classes are called unchanged; a test with a fake auth method checks the delegation.
