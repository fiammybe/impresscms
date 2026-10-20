## 1. Remember me (D1)

- [x] 1.1 `App\Security\RememberMe\TokenProvider` on DBAL (`<prefix>_rememberme_token`), with `deleteTokensOf()`; the table in `install/sql/mysql.structure.sql`
- [x] 1.2 Firewall `remember_me` (cookie `ICMSREMEMBERME`, one week, `rememberme` parameter); `App\Security\Authenticator\Password` adds the badge only with the preference `remember_me` on
- [x] 1.3 `App\Session\Host::logout()` through `Security::logout(false)`, so the token and the cookie go
- [x] 1.4 `plugins/preloads/autologin.php`, `session_confirm.php` and the autologin cookie block of `include/checklogin.php` removed
- [x] 1.5 `App\Session\Host` reads the token with the request on Symfony's request stack: on a legacy page the kernel has already popped it, and the lazy remember-me login needs it

## 2. Login links and reset links (D2-D4)

- [x] 2.1 Firewall `login_link` (`check_post_only`, signed with id, login name and password hash, 15 minutes, once)
- [x] 2.2 `App\Controller\Auth\Link`: the confirmation page of a link
- [x] 2.3 `App\Controller\Auth\Mail` on `/lostpass.php`: same-site check, rate limiter `icms_mail_link`, login or reset link, registration link for an unknown address, one answer; the legacy `lostpass.php` only redirects
- [x] 2.4 `App\Controller\Auth\Password`: the new password after a reset link (15 minutes, CSRF, `minpass`), forgets remembered devices
- [x] 2.5 `App\Security\Login\Responder`: success and failure handler of both logins
- [x] 2.6 The lost-password box: e-mail field, buttons `op=mailpasswd` and `op=loginlink` (`system_userform.html`, `user.php`); language strings in all five languages

## 3. Mail (D5)

- [x] 3.1 `symfony/mailer`; `App\Mail\Transport\Factory` (tagged `mailer.transport_factory`) with `icms://` (`Legacy`, from the mail preferences) and `icms+dir://` (`Directory`, for tests); `App\Mail\Sender`
- [x] 3.2 A mail that cannot be sent is logged, not thrown: the answer to a link request stays the same

## 4. Pages inside the theme (D6)

- [x] 4.1 `App\Front\Page` and `htdocs/include/host_page.php`; content closures, so that controllers never boot legacy before the page
- [x] 4.2 Twig templates `templates/auth/confirm.html.twig` and `password.html.twig`

## 5. Migration

- [x] 5.1 System update step 52 (`ICMS_SYSTEM_DBVERSION` 52): creates `rememberme_token`, deletes the removed files; waits while step 51 has hashes left

## 6. Tests and docs

- [x] 6.1 `tests/Http/EmailLinkTest.php`: login link, confirmation, single use, unknown address, reset, remembered login with rotation, logout
- [x] 6.2 `HostUpgradeTest` expects dbversion 52; the snapshot of the login form re-recorded
- [x] 6.3 Test environment: `APP_SECRET` in `phpunit.xml.dist` (signatures of login links and remember-me need it); mails to `var/test/mail`
- [x] 6.4 `docs/symfony-host.md`: remember me, links, mail, `App\Front\Page`
- [x] 6.5 Verify on the Herd test site: update to 52, remember me, a login link and a reset link
  - Done on `pre3.test`, with mail written to a directory for the check:
    - the update reached dbversion 52, created `icms_rememberme_token` and removed `session_confirm.php` and the autologin preload;
    - a remembered login came back on a legacy page from the cookie alone, the token rotated, and logout deleted it;
    - a login link showed the confirmation inside the theme, opening did not log in, the confirmation did;
    - a reset link led to the new password form, the password was saved as Argon2id, and the password login still works.
  - Two links of one user made in the same second are the same link (a reset link is a login link plus `reset=1`), so the second counts as used.
