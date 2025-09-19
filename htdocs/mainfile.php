<?php
// ImpressCMS multi-site bootstrap stub with domain mapping and safety checks
// Backward compatible: if no per-domain config is found, falls back to installer

// Prevent direct re-entry
if (defined('XOOPS_MAINFILE_INCLUDED')) {
    return;
}

$__icms_base = __DIR__;

// Resolve current host (lowercase, strip port)
$__host = isset($_SERVER['HTTP_HOST']) ? strtolower($_SERVER['HTTP_HOST']) : '';
if ($__host !== '') {
    $pos = strpos($__host, ':');
    if ($pos !== false) $__host = substr($__host, 0, $pos);
}

// Optional alias map: point aliases to a canonical host (edit as needed)
$ICMS_MULTISITE_HOST_ALIASES = array(
    // 'www.example.com' => 'example.com',
);
if (isset($ICMS_MULTISITE_HOST_ALIASES[$__host])) {
    $__host = $ICMS_MULTISITE_HOST_ALIASES[$__host];
}

// Try domain-specific config files in priority order
$__candidates = array();
if ($__host !== '') {
    $__candidates[] = "$__icms_base/mainfile.$__host.php";
    // also try without leading 'www.'
    if (strpos($__host, 'www.') === 0) {
        $__candidates[] = "$__icms_base/mainfile." . substr($__host, 4) . ".php";
    }
}
$__candidates[] = "$__icms_base/mainfile.default.php"; // optional site-default

$__loaded = false;
foreach ($__candidates as $__file) {
    if (is_file($__file)) {
        require $__file; // should define XOOPS_ROOT_PATH, XOOPS_TRUST_PATH, XOOPS_URL, DB creds
        $__loaded = true;
        break;
    }
}

// Minimal safe fallbacks (when no per-site config is present)
if (!$__loaded) {
    // If hitting installer already, don't loop
    $script = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
    if (strpos($script, '/install/') === false && is_dir($__icms_base . '/install')) {
        header('Location: install/index.php');
        exit();
    }
    // derive basic paths as last resort, to avoid notices in constants.php
    if (!defined('XOOPS_ROOT_PATH')) define('XOOPS_ROOT_PATH', $__icms_base);
    if (!defined('XOOPS_TRUST_PATH')) define('XOOPS_TRUST_PATH', $__icms_base . '/var');
    if (!defined('XOOPS_URL')) {
        $https = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
                 || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        $scheme = $https ? 'https' : 'http';
        $host = $__host ?: 'localhost';
        $reqUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        // build base url without path
        define('XOOPS_URL', $scheme . '://' . $host);
    }
}

// Mark as included before proceeding
if (!defined('XOOPS_MAINFILE_INCLUDED')) {
    define('XOOPS_MAINFILE_INCLUDED', 1);
}

// Bootstrap the system
require_once $__icms_base . '/include/common.php';
