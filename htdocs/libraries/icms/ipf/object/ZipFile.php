<?php
/**
 * IPF zip file object
 */

defined('ICMS_ROOT_PATH') or die('ImpressCMS root path not defined');

class icms_ipf_object_ZipFile extends icms_ipf_object_DiskFile {
public function getEntryCount() {
if (!class_exists('ZipArchive')) {
return null;
}

$path = $this->getVar('path', 'n');
if (!$path || !is_file($path)) {
return null;
}

$zip = new ZipArchive();
if ($zip->open($path) !== true) {
return null;
}

$count = (int) $zip->numFiles;
$zip->close();
return $count;
}
}
