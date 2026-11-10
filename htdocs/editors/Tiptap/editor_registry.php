<?php
/**
 * Tiptap adapter for ImpressCMS
 *
 * @license http://www.fsf.org/copyleft/gpl.html GNU public license
 */
global $icmsConfig;

$current_path = __FILE__;
if (DIRECTORY_SEPARATOR != '/') {
    $current_path = str_replace(strpos($current_path, '\\\\', 2) ? '\\\\' : DIRECTORY_SEPARATOR, '/', $current_path);
}
$root_path = dirname($current_path);

$icmsConfig['language'] = preg_replace('/[^a-z0-9_\-]/i', '', $icmsConfig['language']);
if (!@include_once $root_path . '/language/' . $icmsConfig['language'] . '.php') {
    include_once $root_path . '/language/english.php';
}

return $config = array(
    'name' => 'Tiptap',
    'class' => 'icmsFormTiptap',
    'file' => $root_path . '/formTiptap.php',
    'title' => _ICMS_EDITOR_TIPTAP,
    'order' => 4,
);
