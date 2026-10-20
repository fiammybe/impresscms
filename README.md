![](https://github.com/ImpressCMS/impresscms/blob/HEAD/htdocs/uploads/imagemanager/logos/img482278e29e81c.png?raw=true)

![GitHub Release](https://img.shields.io/github/v/release/impresscms/impresscms?display_name=release&logo=github&style=flat-square)
![GitHub Release](https://img.shields.io/github/v/release/impresscms/impresscms?include_prereleases&display_name=release&logo=github&style=flat-square)

[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2FImpressCMS%2Fimpresscms.svg?type=shield&issueType=license&style=flat-square)](https://app.fossa.com/projects/git%2Bgithub.com%2FImpressCMS%2Fimpresscms?ref=badge_shield&issueType=license&style=flat-square)
![Code Climate coverage](https://img.shields.io/codeclimate/coverage-letter/ImpressCMS/impresscms?logo=codeclimate&style=flat-square)


![Code Climate maintainability](https://img.shields.io/codeclimate/maintainability/ImpressCMS/impresscms?logo=codeclimate&style=flat-square) <a title="Crowdin" target="_self" href="https://impresscms.crowdin.com/impresscms"><img src="https://badges.crowdin.net/e/f7817e813865fde0509c454ad1ee4c11/localized.svg" /></a>

![Mastodon Follow](https://img.shields.io/mastodon/follow/109613696614549139?domain=https%3A%2F%2Fphpc.social&style=flat-square&logo=mastodon)
<a href="https://www.facebook.com/ImpressCMS/">
<img src="https://img.shields.io/badge/facebook-%3F%3F%3F-%233C5A99.svg?logo=facebook&style=flat-square" alt="FaceBook" /></a>



# ImpressCMS 3.0.0 Alpha 1
ImpressCMS is a community developed Content Management System build on PHP and MySQL with a focus on speed, support for multiple languages and security

> **This is ImpressCMS 3, a pre-release.** It is under development on the `pre-3` branch and is not meant for production sites yet. The stable line is ImpressCMS 2.0.x, on the `2.0.x` branch.
>
> ImpressCMS 3 runs inside a [Symfony](https://symfony.com/) 7.4 application, the *host*. Pages move to Symfony one at a time: Symfony routes answer directly, and every other request is executed by the existing ImpressCMS code, so your modules, themes and the `icms::` API keep working. See [docs/symfony-host.md](docs/symfony-host.md).

This tool makes maintaining a website as easy as writing a word document.

ImpressCMS is the ideal tool for multi-lingual websites : both the user interface and the content itself can handle multiple languages.

ImpressCMS has these features:
<details>
	<summary>📦 Modules support</summary>
	<blockquote>Website content is managed by separate content modules/web applications. Simply install the module which has the features you need: a news module, forum module, photo album module, there are many many third party modules to choose from.</blockquote>
</details>
<details>
	<summary>🗃️ Database-driven</summary>
	<blockquote>ImpressCMS uses a database to store the data required for running your ImpressCMS site. MySQL and MariaDB is currently supported. Support for other DBMS's is coming soon.</blockquote>
</details>
<details>
	<summary>🌈 Theme-based skinnable interface</summary>
	<blockquote>ImpressCMS is driven by a powerful theme system. Both admins and users can change the look of the entire web site with just a click of a mouse. There are also hundreds of quality themes available for download!</blockquote>
</details>
<details>
	<summary>👥 Versatile Group Permissions System</summary>
	<blockquote>Powerful and user-friendly permissions system which enables administrators to set permissions by group for any registered or all anoymous users.</blockquote>
</details>
<details>
	<summary>🌐 Multi-byte Language Support</summary>
	<blockquote>Fully supports multi-byte languages, including Japanese, Simplified and Traditional Chinese, Korean, etc.</blockquote>
</details>

## What can you do with it?

ImpressCMS can be used for many types of websites. The system is highly scalable and it can be used for example as an intranet for a company with 20,000 employees as well as for building a simple 5-page website for the promotion of your company.
The system is extremely useful for managing online communities because it has the ability to create user groups and assign permissions for managing content to each different group.

For each type of website ImpressCMS offers different functionality with a collection of free modules that are available on [https://www.impresscms.org/modules/downloads/](https://www.impresscms.org/modules/downloads/). A few examples of what you can do with them:

* Publish news of your organization
* Let visitors contact you through a state of the art customizable contact form
* Create and manage articles
* Add a forum to your site
* Sell products through your website using an online store
* ...and many more


## Requirements

| | |
|---|---|
| **PHP** | 8.2, 8.3 or 8.4 |
| **PHP extensions** | `gd`, `json`, `mbstring`, `mysqli`, `pcre`, `pdo` (with `pdo_mysql`), `session`, `xml`, `zlib` |
| **Database** | MySQL or MariaDB |
| **Web server** | Apache or LiteSpeed (with `mod_rewrite` and `AllowOverride`), nginx, FrankenPHP/Caddy, or Laravel Herd. It **must send every PHP request to `index.php`**: see [docs/server/](docs/server/) |
| **Folders** | a *trust path* outside the web root, writable by PHP, and the writable folders the installer lists |

The `sodium` extension is recommended: passwords are hashed with Argon2id, and with bcrypt where Argon2 is not available.

A PHP version that is too old, or one without a required extension, gets a readable requirements page instead of an error. The minimum versions come from `composer.json`.

## Installation

1. Download the package, or build it ([see below](#from-a-repository-checkout)), and unzip it into your web root (for example `public_html/`).
2. Make the web server send every PHP request to `index.php`. On Apache and LiteSpeed the installer writes `.htaccess` from `htaccess.txt` when there is none; otherwise rename `htaccess.txt` to `.htaccess` yourself. For nginx, FrankenPHP and Laravel Herd, use the examples in [docs/server/](docs/server/).
3. Open the site in a browser (or `/install/`) and follow the steps of the installer.

The installer asks for a *trust path*: a folder outside the web root for everything that must not be public. It installs the Symfony host there from the package's `trust_dist/` folder. That covers the application, its Composer libraries and the site's settings in `.env.local`. The installer refuses to continue while the web server does not send every PHP request to `index.php`, and tells you what to configure.

### Upgrading

From ImpressCMS 2.0.x: **make a backup of the database and the files first.** Upload the new files over your site, log in as webmaster and update the system module. The update installs the host into your trust path once the web server sends every PHP request to `index.php`; until then it explains what to configure. It also wraps the stored passwords in Argon2id; every user keeps the password they have.

### From a repository checkout

```bash
composer install       # in the repository root
php bin/build-dist     # creates htdocs/trust_dist/
```

Point a web server at `htdocs/` and open the site to run the installer. To build a package for a hosting account, run `php bin/build-package`. It writes `var/dist/impresscms-<version>-<commit>.zip` from the last commit; unzip it straight into the web root.

`composer test` runs the tests (create the test site first with `php bin/test-db`).

More detailed installation instructions for ImpressCMS 2 are available [on the ImpressCMS site](https://www.impresscms.org/modules/simplywiki/index.php?page=Installation).

## Do you need any help?

You can find our official documentation at [our wiki](https://www.impresscms.org/modules/simplywiki/).

Also, help could be found on [our official forums](https://www.impresscms.org/modules/iforum/).

## How to contribute?
### Code contribution
Open an [issue](https://github.com/ImpressCMS/impresscms/issues/new) or send us a [pull request](https://github.com/ImpressCMS/impresscms/pulls). If you plan on working on more than just a simple bugfix, discuss your plans first on the forums.

All active development branches are named in format `MAJOR.MINOR.x`. So if you want to fix or add something new, you should branch from most similar looking branch.

E.g.: if you want to do with latest **2.0** code, you need to branch from `2.0.x` branch. Work on **ImpressCMS 3** goes to the `pre-3` branch.

If you not sure how can be possible to work with Git or/and GitHub, try [interactive GitHub tutorial](https://skills.github.com).
### Translation
ImpressCMS is built to work with multiple languages, for the user interface of the administration interface to the content managed on the site.

We use Crowdin to manage our translations, please check out [https://impresscms.crowdin.com](https://impresscms.crowdin.com/u/projects/1)
## License
<a href="https://app.fossa.io/projects/git%2Bgithub.com%2FImpressCMS%2Fimpresscms?ref=badge_large"><img src="https://app.fossa.io/api/projects/git%2Bgithub.com%2FImpressCMS%2Fimpresscms.svg?type=large" alt="FOSSA Status" align="right" /></a>
ImpressCMS is licensed right now in <a href="https://github.com/ImpressCMS/impresscms/blob/HEAD/LICENSE">GPL 2.0 license</a> but is depended on libraries that uses other compatible open source licenses.

Quick summary what GPL 2.0 really means can be found at [TLDRLegal website](https://tldrlegal.com/license/gnu-general-public-license-v2).

We use FOSSA to detect if there are any incompatibilities and if there is any solve them. The graphic on the right is used to iliustrate what licenses are used when project is installed.
