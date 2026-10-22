<?php
// Preloads administration (served by the Symfony host at index.php/admin/preloads)
define('_MD_AM_PRELOADS_INTRO', 'Preloads hook into every request of the site. A disabled preload is no longer loaded from the next request on; its file stays in place.');
define('_MD_AM_PRELOADS_NAME', 'Preload');
define('_MD_AM_PRELOADS_EVENTS', 'Events');
define('_MD_AM_PRELOADS_STATE', 'Status');
define('_MD_AM_PRELOADS_ACTION', 'Action');
define('_MD_AM_PRELOADS_ENABLED', 'Enabled');
define('_MD_AM_PRELOADS_DISABLED', 'Disabled');
define('_MD_AM_PRELOADS_ENABLE', 'Enable');
define('_MD_AM_PRELOADS_DISABLE', 'Disable');
define('_MD_AM_PRELOADS_NOEVENTS', 'none');
define('_MD_AM_PRELOADS_NONE', 'No preloads are installed.');
define('_MD_AM_PRELOADS_SECURITY', 'This preload protects the site. Disabling it weakens its security.');
define('_MD_AM_PRELOADS_ENABLEDOK', 'The preload %s is enabled. It is loaded again from the next request on.');
define('_MD_AM_PRELOADS_DISABLEDOK', 'The preload %s is disabled. It is no longer loaded from the next request on.');
define('_MD_AM_PRELOADS_UNKNOWN', 'There is no preload named %s.');
define('_MD_AM_PRELOADS_NOTWRITABLE', 'The disabled preloads cannot be stored: %s must be writable by the web server.');
