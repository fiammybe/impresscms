## ADDED Requirements

### Requirement: Legacy language files feed one Symfony Translator
The host SHALL provide one `TranslatorInterface` service whose catalogues come from the existing constant-based language files, without changes to those files. Each constant SHALL become a message whose key is the constant name and whose value is the defined string. Reading the files SHALL NOT define any global constant.

#### Scenario: No global side effects
- **WHEN** the Translator builds a catalogue for a language whose files have not been loaded by legacy code in that request
- **THEN** no constant from those files is defined afterwards

#### Scenario: Value referencing another constant
- **WHEN** a language file defines a value as `ICMS_URL . '/user.php'`
- **THEN** the message value contains the site's URL followed by `/user.php`

### Requirement: Domains follow the core and installed modules
Core files in `<web root>/language/<dir>/`, including `admin/`, SHALL be in domain `icms`. The files of each installed module in `<web root>/modules/<module>/language/<dir>/` SHALL be in a domain named after the module's dirname.

#### Scenario: Core string from Symfony code
- **WHEN** a Symfony controller calls `$translator->trans('_US_NICKNAME', [], 'icms', 'en')`
- **THEN** it gets the value defined in `language/english/user.php`

#### Scenario: Module string
- **WHEN** code calls `trans('_MD_AM_AUTOTASKS', [], 'system', 'en')`
- **THEN** it gets the value from the system module's English language files

### Requirement: Catalogues do not depend on what legacy loaded first
A message SHALL be present in the catalogue even when the language file guards its definition with `if (!defined('X'))` and legacy code already defined `X` earlier in the same request.

#### Scenario: Guarded definition after legacy load
- **WHEN** legacy has defined `_AM_DBUPDATED` and the Translator then builds the catalogue of the file that defines it under `if (!defined('_AM_DBUPDATED'))`
- **THEN** `_AM_DBUPDATED` is in that catalogue with that file's value

### Requirement: Locales follow the installed languages
Each language directory SHALL map to the locale named by the `_LANGCODE` in its `global.php`, normalised to Symfony's form (`zh-cn` → `zh_CN`). The Translator's current locale SHALL be the locale of the site language (`$icmsConfig['language']`) for every request. English (`en`) SHALL be the fallback locale. A directory without `_LANGCODE` SHALL be skipped and logged.

#### Scenario: Current language
- **WHEN** the site language is `nederlands` and code calls `trans('_US_NICKNAME', [], 'icms')` without a locale
- **THEN** it gets the Dutch value

#### Scenario: Missing translation falls back to English
- **WHEN** a key exists in `english` but not in `italiano` and the locale is `it`
- **THEN** the English value is returned

#### Scenario: Other language within the same request
- **WHEN** the current locale is `en` and code calls `trans('_US_NICKNAME', [], 'icms', 'fr')`
- **THEN** it gets the French value, and the current locale is still `en` afterwards

### Requirement: Language files are read only inside a booted runtime
Language resources SHALL be registered and evaluated only after the legacy runtime has booted in that process, never during container compilation or `cache:warmup`. A file that throws when evaluated SHALL be logged and skipped without failing the request. Files that are not constant lists (`xoopsmailerlocal.php`, `pdf.php`) SHALL never be registered.

#### Scenario: Cache warmup without a site
- **WHEN** `bin/console cache:warmup` runs in a trust path
- **THEN** it completes without evaluating any language file

#### Scenario: Broken module language file
- **WHEN** an installed module's language file has a fatal error when evaluated
- **THEN** that file's messages are missing, an error naming the file is logged, and the page still renders

### Requirement: Catalogues follow installation changes
The cached catalogues SHALL be invalidated when a module is installed, updated or uninstalled, or a language is added or removed.

#### Scenario: Module uninstalled
- **WHEN** a module is uninstalled and then a page translates one of its keys in the module's domain
- **THEN** the key itself is returned

#### Scenario: Module installed
- **WHEN** a module is installed
- **THEN** its strings are available in its domain on the next request without a manual cache clear

### Requirement: Legacy constant loading is unchanged
`icms_loadLanguageFile()` and the constants it defines SHALL behave as before. The Translator SHALL be an additional way to read the same strings, not a replacement.

#### Scenario: Legacy page
- **WHEN** a legacy page calls `icms_loadLanguageFile('core', 'user')` and echoes `_US_NICKNAME`
- **THEN** the output is the same as before this change
