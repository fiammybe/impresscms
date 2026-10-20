## Context

**Remember me.** The legacy cookie (`autologin_uname`, `autologin_pass`) is `md5(pass . ICMS_DB_PASS . ICMS_DB_PREFIX . date)`, checked by a preload. `ICMS_DB_PASS` is not defined on PHP 8, so the login with "remember me" ends in a fatal error. The characterization snapshot `two-cookies` recorded that until `symfony-password-login` dropped the cookie.

**Lost password.** `lostpass.php` mails a code `sha1(substr(pass, -5) . last_login)`. Following it generates and mails a new password.

**Mail.** Legacy code sends mail with PHPMailer (`icms_messaging_Handler`). The preferences are `mailmethod` (`mail`, `sendmail`, `smtp`, `smtpauth`), `smtphost`, `smtpuser`, `smtppass`, `smtpsecure`, `smtpauthport`, `sendmailpath`, `from` and `fromname`.

## Goals / Non-Goals

**Goals:**
- Remember-me that is safe (persistent, rotating tokens) and works.
- A login by link, safe against mail scanners and against enumerating addresses.
- No password ever travels by mail.
- One mail configuration.

**Non-Goals:**
- Replacing legacy mail (notifications, PMs).
- Registration by link: an unknown address only receives the link to the registration form.

## Decisions

### D1. Remember me: persistent tokens in our own provider

`App\Security\RememberMe\TokenProvider` implements Symfony's `TokenProviderInterface` on DBAL. It uses the table `<prefix>_rememberme_token` (`series`, `value`, `lastUsed`, `class`, `username`).

Symfony's `DoctrineTokenProvider` cannot be used, because its table name is fixed and ImpressCMS tables carry the site prefix.

**Firewall configuration:**
- `remember_me` with `token_provider`, `remember_me_parameter: rememberme`, a lifetime of one week (as `ICMS_AUTOLOGIN_LIFETIME`) and the cookie `ICMSREMEMBERME`.
- The password authenticator adds an enabled `RememberMeBadge` only when the preference `remember_me` is on and the form sent `rememberme`.

**Logout.** `App\Session\Host::logout()` runs `Security::logout(false)`. Symfony's remember-me listener deletes the token and clears the cookie, then the host invalidates the session.

### D2. Login links: Symfony's `login_link`, confirmed by a POST

```yaml
login_link:
    check_route: icms_auth_link_check
    check_post_only: true
    signature_properties: [id, loginName, password]
    lifetime: 900
    max_uses: 1
```

- **The `password` property** (the hash) makes every outstanding link invalid once the password changes.
- **`check_post_only`:** a GET of the link reaches `App\Controller\Auth\Link`, which shows a confirmation button posting to the same URL. Mail scanners only GET.

### D3. Requesting links: the lost-password box

**The form.** The lost-password box gets two buttons on the one e-mail field: `op=mailpasswd` (reset link) and `op=loginlink` (login link).

**The route.** `App\Controller\Auth\Mail` takes over `POST /lostpass.php`.
- It applies the same-site check of the login and a rate limit (`icms_mail_link`: 3 per 15 minutes per address and IP).
- It finds the account by e-mail address: exactly one, and active.
- It mails the link. For an unknown address, it mails the registration link when `allow_register` is on.
- It always answers with the same redirect and message.

`GET /lostpass.php` redirects to the login page. The legacy `lostpass.php` only serves sites without a host (an upgrade before the update), and only redirects.

**A reset link** is a login link with `reset=1` appended. The parameter is not signed, but it only chooses where to go after the login.
- **After a reset link**, the session gets `icms_password_reset` for 15 minutes, and `App\Controller\Auth\Password` shows a form for a new password (with Symfony CSRF).
- **Saving** stores the hash of the shared hasher and clears `pass_expired`.

### D4. One response for every login

`App\Security\Login\Responder` is the success and failure handler of the password authenticator and of the login link. It does what the password authenticator did:
- `Icms\Core\Session::loggedIn()`;
- `$_SESSION['redirect_message']`;
- the redirect to the validated return URL.

It sends a reset link to the new-password form, and an expired password to the reset form.

### D5. Mail: Symfony Mailer with a transport from the preferences

`App\Mail\Transport\Factory` registers two DSN schemes:
- **`icms://default`** builds the transport from the preferences when the first mail is sent:
  - `mail` becomes `native://default`;
  - `sendmail` becomes `sendmail://default?command=<path> -bs`;
  - `smtp` becomes `smtp://host:port`;
  - `smtpauth` becomes `smtp(s)://user:pass@host:port`, where `smtpsecure=ssl` means implicit TLS.
- **`icms+dir://default?path=…`** writes each message as an `.eml` file, so that the test environment can read them.

The sender is `from` (the webmaster's address if empty) with `fromname` (else the site name).

### D6. Symfony pages inside the theme

`App\Front\Page::render()` puts a content closure aside and returns the bridge's `LegacyExecutionResponse` for `htdocs/include/host_page.php`. host.php runs that script at file scope like any legacy page:
1. it includes `mainfile.php`, then `include/common.php` once;
2. it includes `header.php`;
3. it calls the closure for the title and the HTML, and echoes the HTML;
4. it includes `footer.php`.

Globals, blocks and the theme work as usual. Without a pending page (a direct request), the script answers 404. Twig templates in `templates/auth/` produce the HTML.

The content is a closure because of the legacy boot:
- A controller must not boot legacy itself for the language strings. A headless boot (`App\Legacy\LegacyRuntime`) runs stages without the preloads, and the page's own boot then skips them: the page fails.
- The closure runs after `header.php`, when the page type's language (`user`) is loaded.
- The database connection, which the session and the preferences need, does read `mainfile.php` headless. Then the page's own include of `mainfile.php` does nothing, so `host_page.php` includes `common.php` itself.
- When the closure runs, the request has left Symfony's request stack. So a controller computes what needs the session, such as a CSRF token, beforehand.

## Risks / Trade-offs

- **Existing autologin cookies stop working.** Accepted: they ended in a fatal error.
- **A forwarded login link logs the receiver in once.** That is inherent to login links. The link is short-lived and single-use, and requires the click.
- **`native://` on Windows** uses PHP's SMTP ini settings, as `mail()` did.
