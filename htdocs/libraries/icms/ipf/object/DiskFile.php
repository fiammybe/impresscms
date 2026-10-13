<?php
/**
 * IPF disk file object
 *
 * @copyright The ImpressCMS Project http://www.impresscms.org/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @category ICMS
 * @package Ipf
 * @subpackage Object
 */

defined('ICMS_ROOT_PATH') or die('ImpressCMS root path not defined');

class icms_ipf_object_DiskFile extends icms_ipf_Object {
public function __construct(&$handler = null) {
if ($handler) {
parent::__construct($handler);
}

$this->initVar('path', XOBJ_DTYPE_TXTBOX, '', true, null, '', false);
$this->initVar('dirname', XOBJ_DTYPE_TXTBOX, '', false, null, '', false);
$this->initVar('basename', XOBJ_DTYPE_TXTBOX, '', false, null, '', false);
$this->initVar('extension', XOBJ_DTYPE_TXTBOX, '', false, null, '', false);
$this->initVar('size', XOBJ_DTYPE_INT, 0, false);
$this->initVar('mtime', XOBJ_DTYPE_INT, 0, false);
$this->initVar('is_readable', XOBJ_DTYPE_INT, 0, false);
$this->initVar('is_writable', XOBJ_DTYPE_INT, 0, false);
$this->initVar('mime_type', XOBJ_DTYPE_TXTBOX, '', false, null, '', false);
}

public function loadFromPath($path) {
if (!is_string($path) || $path === '' || !is_file($path)) {
$this->setErrors('Disk file not found: ' . $path);
return false;
}

$pathInfo = pathinfo($path);
$this->setVar('path', $path);
$this->setVar('dirname', isset($pathInfo['dirname']) ? $pathInfo['dirname'] : '');
$this->setVar('basename', isset($pathInfo['basename']) ? $pathInfo['basename'] : '');
$this->setVar('extension', isset($pathInfo['extension']) ? strtolower($pathInfo['extension']) : '');
$this->setVar('size', (int) filesize($path));
$this->setVar('mtime', (int) filemtime($path));
$this->setVar('is_readable', is_readable($path) ? 1 : 0);
$this->setVar('is_writable', is_writable($path) ? 1 : 0);
$this->setVar('mime_type', $this->detectMimeType($path));
$this->unsetNew();
$this->unsetDirty();

return true;
}

public function rename($newName) {
if (!is_string($newName) || $newName === '' || basename($newName) !== $newName) {
$this->setErrors('Invalid new file name: ' . $newName);
return false;
}

$path = $this->getVar('path', 'n');
if (!$path) {
$this->setErrors('No file path set on disk file object');
return false;
}

$targetPath = dirname($path) . DIRECTORY_SEPARATOR . $newName;
return $this->renameToPath($targetPath);
}

public function delete() {
$path = $this->getVar('path', 'n');
if (!$path || !is_file($path)) {
$this->setErrors('Disk file not found for delete: ' . $path);
return false;
}

if (!@unlink($path)) {
$this->setErrors('Unable to delete file: ' . $path);
return false;
}

return true;
}

public function moveTo($targetDir) {
if (!is_string($targetDir) || $targetDir === '' || !is_dir($targetDir)) {
$this->setErrors('Target directory does not exist: ' . $targetDir);
return false;
}

$path = $this->getVar('path', 'n');
if (!$path) {
$this->setErrors('No file path set on disk file object');
return false;
}

$targetPath = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $this->getVar('basename', 'n');
return $this->renameToPath($targetPath);
}

public function copyTo($targetDir) {
if (!is_string($targetDir) || $targetDir === '' || !is_dir($targetDir)) {
$this->setErrors('Target directory does not exist: ' . $targetDir);
return false;
}

$path = $this->getVar('path', 'n');
if (!$path || !is_file($path)) {
$this->setErrors('Disk file not found for copy: ' . $path);
return false;
}

$targetPath = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $this->getVar('basename', 'n');
if (!@copy($path, $targetPath)) {
$this->setErrors('Unable to copy file to: ' . $targetPath);
return false;
}

return true;
}

protected function renameToPath($targetPath) {
$path = $this->getVar('path', 'n');
if (!$path || !is_file($path)) {
$this->setErrors('Disk file not found for rename/move: ' . $path);
return false;
}

if (!@rename($path, $targetPath)) {
$this->setErrors('Unable to rename/move file to: ' . $targetPath);
return false;
}

return $this->loadFromPath($targetPath);
}

protected function detectMimeType($path) {
if (function_exists('mime_content_type')) {
$mimeType = @mime_content_type($path);
if (is_string($mimeType)) {
return $mimeType;
}
}

return '';
}
}
