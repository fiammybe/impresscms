<?php

// Constants that ImpressCMS defines at runtime (mainfile.php, include/constants.php),
// for static analysis only.
define('ICMS_ROOT_PATH', __DIR__ . '/htdocs');
define('ICMS_TRUST_PATH', __DIR__ . '/var/trust');
define('ICMS_INCLUDE_PATH', ICMS_ROOT_PATH . '/include');
define('ICMS_LIBRARIES_PATH', ICMS_ROOT_PATH . '/libraries');
define('ICMS_GROUP_ADMIN', 1);
define('ICMS_GROUP_USERS', 2);
define('ICMS_GROUP_ANONYMOUS', 3);
define('_INTL_LOCALE', 'en_US');
define('ICMS_URL', 'http://localhost');
define('_GLOBAL_LEFT', 'left');
define('ICMS_CACHE_PATH', ICMS_ROOT_PATH . '/cache');

// Icms\Ipf\Entity loads a language file when its class file is included, which
// the analysis does to reflect the classes that extend it (Icms\Capability\Config)
if (!function_exists('icms_loadLanguageFile')) {
    function icms_loadLanguageFile(string $module, string $file, bool $admin = false): void
    {
    }
}
