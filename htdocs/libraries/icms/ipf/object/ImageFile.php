<?php
/**
 * IPF image file object
 */

defined('ICMS_ROOT_PATH') or die('ImpressCMS root path not defined');

class icms_ipf_object_ImageFile extends icms_ipf_object_DiskFile {
public function getDimensions() {
$path = $this->getVar('path', 'n');
if (!$path || !is_file($path)) {
return false;
}

$dimensions = @getimagesize($path);
if (!is_array($dimensions)) {
return false;
}

return array(
'width' => isset($dimensions[0]) ? (int) $dimensions[0] : 0,
'height' => isset($dimensions[1]) ? (int) $dimensions[1] : 0,
);
}
}
