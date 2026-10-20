## Why

The rest of ImpressCMS' login is still unsafe or broken:
- **Remember me.** The cookie is derived from the password hash and the database password (`plugins/preloads/autologin.php`). On PHP 8 it ends in a fatal error, because `ICMS_DB_PASS` is undefined.
- **Lost password.** `lostpass.php` derives its reset code from the hash and mails a new password in plain text.
- **No password-free login.** Users without a password manager have no way to log in without a password.

The host now owns the session, the identity and the password login (`symfony-owns-the-session`, `symfony-password-login`). Symfony's remember-me and login-link components can therefore replace this code.

## What Changes

- **Remember me.** Symfony's `remember_me` with persistent tokens in a new `rememberme_token` table, created by system update step 52 and the installer.
  - The token rotates on every use and is deleted at logout.
  - It is offered when the "remember me" preference is on, through the `rememberme` field of the login form.
  - `plugins/preloads/autologin.php` and `session_confirm.php` are removed.
- **Login link by e-mail.** The lost-password box of the login page gets a "send me a login link" button.
  - The link is signed and lasts 15 minutes. It works once, and stops working when the password changes.
  - Opening it shows a confirmation button; only the click logs in. A mail scanner that follows the link logs nobody in.
  - An unknown address gets a registration link when registration is open.
  - The response never reveals whether an address is known.
- **Password reset by link.** "Send password" mails a reset link (the same kind of link) to a form for a new password. A password is never mailed again.
- **Mail through Symfony Mailer.** The transport is built from the ImpressCMS mail preferences (`mailmethod`, SMTP, sendmail, sender), so there is one configuration. In the test environment, mails are written to files.
- **Symfony pages inside the theme.** `App\Front\Page` renders the HTML of a Symfony controller between `header.php` and `footer.php`, through the legacy bridge. The confirmation and new-password pages use it.
- **Rate limits.** Mails for links: 3 per 15 minutes per address and IP.

## Capabilities

### New Capabilities
None.

### Modified Capabilities
- `authentication`: remember-me, login links and password reset by link.

## Impact

- **New dependency:** `symfony/mailer`.
- **Schema:** `rememberme_token` (dbversion 52).
- **Existing autologin cookies** stop working. They could not work on PHP 8 anyway.
- **Code:**
  - Legacy: `htdocs/lostpass.php`, `htdocs/modules/system/templates/system_userform.html`, `htdocs/user.php`, `htdocs/include/checklogin.php`, new `htdocs/include/host_page.php`, removed `htdocs/plugins/preloads/autologin.php` and `htdocs/session_confirm.php`.
  - Host: `src/Mail/*`, `src/Front/Page.php`, `src/Controller/Auth/*`, `src/Security/Login/Responder.php`, `src/Security/RememberMe/TokenProvider.php`, `templates/auth/*`.
