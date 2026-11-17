## RENAMED Requirements

- FROM: `### Requirement: ImpressCMS plugins live outside the vendor tree`
- TO: `### Requirement: ImpressCMS template tags and the db resource are registered as Smarty 5 extensions`

## MODIFIED Requirements

### Requirement: ImpressCMS template tags and the db resource are registered as Smarty 5 extensions
`Icms\View\Tpl` SHALL register the `db` resource and the `xoAppUrl`, `xoImgUrl`, `xoPageNav`, `xoInboxCount`, `xoops_link` and `resized_image` tags through the Smarty 5 extension/plugin API. The `db` resource SHALL come from `imponeer/smarty-db-resource` and `xoAppUrl`, `xoImgUrl`, `xoPageNav` and `xoInboxCount` from `imponeer/smarty-xo`, each wired to ImpressCMS services through their callbacks. ImpressCMS-specific adapters and the tags those packages do not provide (`xoops_link`, `resized_image`) SHALL live under `htdocs/libraries/Icms/View/Smarty/`. Templates SHALL produce the same output as with the ImpressCMS ports they replace.

#### Scenario: Custom tags work with custom delimiters
- **WHEN** a template using `<{xoAppUrl modules/system/admin.php fct=users}>`, `<{xoImgUrl icons/edit.png}>`, `<{xoPageNav pageSize=10 itemsCount=95 offset=20}>` or `<{xoInboxCount assign=count}>` is rendered
- **THEN** it produces the same output as under the ImpressCMS port of that tag

#### Scenario: Image URL with theme override
- **WHEN** the current theme contains `images/icons/edit.png` and a template uses `<{xoImgUrl icons/edit.png}>`
- **THEN** the URL points at the theme's copy

#### Scenario: Template from a non-default set
- **WHEN** the template set is `custom`, it contains a modified `system_block_user.html` and a page requests `db:system_block_user.html`
- **THEN** the source stored for `custom` in the database is rendered

#### Scenario: Theme override in the default set
- **WHEN** the template set is `default` and the current theme contains `modules/system/blocks/system_block_user.html`
- **THEN** `db:system_block_user.html` renders the theme's file, and recompiles when that file's modification time changes

#### Scenario: Template only stored in the database
- **WHEN** the template set is `default`, a row for `x.html` exists in `tplfile` and neither the theme nor the module has the file
- **THEN** `db:x.html` renders the `tpl_source` from the database, as before

#### Scenario: Same database connection
- **WHEN** a `db:` template is fetched during a request
- **THEN** the query runs on the request's shared database connection, not a new one

#### Scenario: Resized images keep file output
- **WHEN** a template uses `<{resized_image file="/uploads/a.jpg" width=120 return="url"}>`
- **THEN** the result is a URL under `<ICMS_URL>/cache/`, not a `data:` URI

## ADDED Requirements

### Requirement: Templates can translate through the shared Translator
`Icms\View\Tpl` SHALL provide the `<{trans}>` block and the `|trans` modifier from `imponeer/smarty-translate`, backed by the Translator of the `translations` capability. The default domain SHALL be `icms`. The `domain` and `locale` parameters SHALL select another domain or language.

#### Scenario: Core string in the current language
- **WHEN** the site language is `nederlands` and a template contains `<{trans}>_US_NICKNAME<{/trans}>`
- **THEN** the Dutch value of `_US_NICKNAME` is rendered

#### Scenario: Module string in another language
- **WHEN** a template contains `<{"_MD_AM_AUTOTASKS"|trans:[]:"system":"fr"}>`
- **THEN** the French value from the system module's language files is rendered, whatever the current language is

#### Scenario: Unknown key
- **WHEN** a template translates a key that no language file defines
- **THEN** the key itself is rendered and the template does not fail

### Requirement: Legacy tag aliases use the maintained compilers
`<{includeq}>` and `<{foreachq}>` SHALL be compiled by `imponeer/smarty-includeq` and `imponeer/smarty-foreachq`, through ImpressCMS subclasses that log the deprecation notice required for legacy tag aliases before delegating.

#### Scenario: Legacy loop
- **WHEN** a template contains `<{foreachq item=u from=$users}><{$u.uname}><{/foreachq}>`
- **THEN** it renders the same as the equivalent `<{foreach}>` and one deprecation notice naming the template is logged
