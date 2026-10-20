## Why

ImpressCMS stores passwords as salted SHA-512 (or older: SHA/RIPEMD/HAVAL with a salt column, or plain MD5), with "5000 iterations" that in fact hash once (`Icms\Core\Password::_rehash()` never feeds the result back). The comparison is not constant-time, the login cuts passwords to 32 characters, and the user is looked up by `pass = <hash>`. A leaked `users` table can be brute-forced quickly.

The host now owns the session and the identity (`symfony-owns-the-session`), so the password login can move to Symfony's security layer: password hashing with automatic migration, login throttling and user checks.

## What Changes

- **Argon2id for all passwords.** New and changed passwords are hashed with Argon2id (bcrypt only where PHP has no Argon2; Symfony's `auto` would choose bcrypt), in the host and in legacy code (`Icms\Core\Password::encryptPass()`), so register, password change, reset and the admin user form need no change.
- **Legacy hashes are wrapped at the upgrade.** System module update step 51 replaces every legacy hash by `$icms-legacy$<parameters>$<argon2id(legacy hash)>`: no weak hash stays in the database. The update works in batches within a time budget and continues on the next run; `bin/console icms:passwords:wrap` does all at once; the admin dashboard warns while hashes are left. The legacy formats stay verifiable (`Icms\Core\Password\Legacy`); a successful login replaces a wrapped or legacy hash by a plain Argon2id hash.
- **The login is a Symfony authenticator.** `App\Security\Authenticator\Password` handles the legacy login form (`POST /user.php` with `op=login`) and the closed-site login form (`xoops_login`), with the same field names, so themes and blocks keep working. It uses the ImpressCMS authentication method: the password hashers for `xoops`, the legacy `ldap`/`ads` classes otherwise. It rejects cross-site form posts, follows the legacy redirect rules and messages, and sends users with an expired password to the reset form.
- **Login throttling** with `symfony/rate-limiter`: 5 failed attempts per user name and IP address per minute.
- **User checks** (`App\Security\User\Checker`): not activated, deactivated, closed site, multiple logins.
- **`include/checklogin.php` only serves sites without the host:** a site that was just uploaded over an older release logs in through ImpressCMS until the system module update installs the host.
- **Removed:** the 32-character cut, the lookup by hash, the "password encryption" preference (`enc_type`) and the forced password expiry when it changed. LDAP provisioning stores an unusable marker instead of an MD5 hash.

## Capabilities

### New Capabilities
- `authentication`: how passwords are stored and migrated, and how the password login, throttling and account checks behave.

### Modified Capabilities
None. (`session-identity` already states that the login runs through the host; `symfony-owns-the-session` words it independently of `include/checklogin.php`.)

## Impact

- **Downgrade:** after the update, ImpressCMS 2.x cannot verify the passwords. Upgrade notes ask for a database backup first.
- **New dependency:** `symfony/rate-limiter`.
- **Code:** `htdocs/libraries/Icms/Core/Password.php`, new `htdocs/libraries/Icms/Core/Password/Legacy.php`, `htdocs/libraries/Icms/Member/Handler.php`, `htdocs/user.php`, `htdocs/include/site-closed.php`, `htdocs/libraries/Icms/Auth/Provisionning.php`, `htdocs/modules/system/include/update.php`, system preferences; host: `config/packages/security.yaml`, `src/Security/*`, new `src/Command/Password/Wrap.php`.
