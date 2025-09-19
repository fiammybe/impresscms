<?php
/**
 * ImpressCMS per-site configuration (default)
 *
 * Copy this file to mainfile.{host}.php for domain-specific overrides, e.g.:
 *   - mainfile.example.com.php
 *   - mainfile.site2.com.php
 *
 * Required constants:
 *  - XOOPS_ROOT_PATH   Absolute filesystem path to htdocs
 *  - XOOPS_TRUST_PATH  Absolute filesystem path to a writable private folder (e.g. var)
 *  - XOOPS_URL         Base URL for this site (no trailing slash)
 *  - XOOPS_DB_TYPE     Database driver: 'pdo.mysql' (recommended) or legacy 'mysql'
 *  - XOOPS_DB_HOST     Database host (e.g., 'localhost')
 *  - ICMS_DB_PORT      Optional DB port (e.g., 3306)
 *  - XOOPS_DB_USER     Database user
 *  - XOOPS_DB_PASS     Database password
 *  - XOOPS_DB_NAME     Database name
 *  - XOOPS_DB_PREFIX   Table prefix (use a unique prefix per site)
 *  - XOOPS_DB_CHARSET  Database charset (e.g., 'utf8mb4')
 *  - XOOPS_DB_PCONNECT 1 for persistent connections, 0 otherwise (recommended: 0)
 */

// Absolute paths
define('XOOPS_ROOT_PATH', __DIR__);
define('XOOPS_TRUST_PATH', __DIR__ . '/var');

// Public base URL (adjust protocol/host)
// Example: 'https://example.com' or 'http://example.local'
define('XOOPS_URL', 'http://example.local');

// Database settings
// Use PDO MySQL driver by default (preferred): 'pdo.mysql'
// For legacy driver (not recommended), use 'mysql'
define('XOOPS_DB_TYPE', 'pdo.mysql');

// Host and optional port
define('XOOPS_DB_HOST', 'localhost');
// Optional: uncomment and set a non-default port
// define('ICMS_DB_PORT', 3306);

// Credentials and database name
define('XOOPS_DB_USER', 'db_user');
define('XOOPS_DB_PASS', 'db_password');
define('XOOPS_DB_NAME', 'db_name');

// Table prefix (choose a unique prefix per site)
define('XOOPS_DB_PREFIX', 'icms');

// Charset and persistence
define('XOOPS_DB_CHARSET', 'utf8mb4');
define('XOOPS_DB_PCONNECT', 0);

// You can add other site-specific constants below if needed.
// Do NOT include or require other files here; htdocs/mainfile.php bootstraps the system.

