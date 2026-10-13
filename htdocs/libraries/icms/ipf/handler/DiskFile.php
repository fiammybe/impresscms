<?php
/**
 * IPF disk file handler
 */

defined('ICMS_ROOT_PATH') or die('ImpressCMS root path not defined');

class icms_ipf_handler_DiskFile {
private $supportedCriteriaFields = array('basename', 'extension', 'size', 'mtime');

public $className = 'icms_ipf_object_DiskFile';
public $keyName = 'path';
public $identifierName = 'basename';

public function create() {
$className = $this->className;
return new $className($this);
}

public function getList($folder, $criteria = null, $sort = 'basename', $order = 'ASC') {
$objects = $this->getObjects($folder, $criteria, $sort, $order);
$ret = array();
foreach ($objects as $object) {
$ret[$object->getVar('path', 'n')] = $object->getVar('basename', 'n');
}
return $ret;
}

public function getObjects($folder, $criteria = null, $sort = 'basename', $order = 'ASC') {
if (!is_string($folder) || $folder === '' || !is_dir($folder)) {
trigger_error('Disk folder not found: ' . $folder, E_USER_WARNING);
return array();
}

$unsupported = array();
$objects = array();
$iterator = new FilesystemIterator($folder, FilesystemIterator::SKIP_DOTS);
foreach ($iterator as $fileInfo) {
if (!$fileInfo->isFile()) {
continue;
}

$fileObject = $this->createObjectFromPath($fileInfo->getPathname());
if (!$fileObject) {
continue;
}

if ($criteria && !$this->matchesCriteria($fileObject, $criteria, $unsupported)) {
continue;
}

$objects[] = $fileObject;
}

if (!empty($unsupported)) {
$unsupported = array_unique($unsupported);
trigger_error('Unsupported disk file criteria fields: ' . implode(', ', $unsupported), E_USER_WARNING);
}

$this->sortObjects($objects, $sort, $order);
$objects = $this->applyRange($objects, $criteria);

return $objects;
}

public function get($path) {
$fileObject = $this->createObjectFromPath($path);
if ($fileObject) {
return $fileObject;
}

$fileObject = $this->create();
$fileObject->setNew();
$fileObject->setErrors('Disk file not found: ' . $path);
return $fileObject;
}

protected function createObjectFromPath($path) {
$className = $this->detectClassForPath($path);
$fileObject = new $className($this);
if (!$fileObject->loadFromPath($path)) {
return false;
}
return $fileObject;
}

protected function detectClassForPath($path) {
$extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$mimeType = '';
if (function_exists('mime_content_type')) {
$detectedMime = @mime_content_type($path);
if (is_string($detectedMime)) {
$mimeType = strtolower($detectedMime);
}
}

if ($mimeType && strpos($mimeType, 'image/') === 0) {
return 'icms_ipf_object_ImageFile';
}
if ($mimeType && strpos($mimeType, 'video/') === 0) {
return 'icms_ipf_object_VideoFile';
}
if ($mimeType && in_array($mimeType, array('application/zip', 'application/x-zip-compressed'))) {
return 'icms_ipf_object_ZipFile';
}

if (in_array($extension, array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'))) {
return 'icms_ipf_object_ImageFile';
}
if (in_array($extension, array('mp4', 'mov', 'avi', 'mkv', 'webm', 'ogv', 'wmv'))) {
return 'icms_ipf_object_VideoFile';
}
if ($extension === 'zip') {
return 'icms_ipf_object_ZipFile';
}

return 'icms_ipf_object_DiskFile';
}

protected function matchesCriteria($fileObject, $criteria, array &$unsupported) {
if (!($criteria instanceof icms_db_criteria_Element)) {
return true;
}

if ($criteria instanceof icms_db_criteria_Compo) {
return $this->matchesComposedCriteria($fileObject, $criteria, $unsupported);
}

if ($criteria instanceof icms_db_criteria_Item) {
return $this->matchesItemCriteria($fileObject, $criteria, $unsupported);
}

return true;
}

protected function matchesComposedCriteria($fileObject, $criteria, array &$unsupported) {
$count = count($criteria->criteriaElements);
if ($count === 0) {
return true;
}

$result = $this->matchesCriteria($fileObject, $criteria->criteriaElements[0], $unsupported);
for ($index = 1; $index < $count; $index++) {
$condition = isset($criteria->conditions[$index]) ? strtoupper($criteria->conditions[$index]) : 'AND';
$next = $this->matchesCriteria($fileObject, $criteria->criteriaElements[$index], $unsupported);
if ($condition === 'OR') {
$result = ($result || $next);
} else {
$result = ($result && $next);
}
}
return $result;
}

protected function matchesItemCriteria($fileObject, $criteria, array &$unsupported) {
$column = $this->getItemCriteriaProperty($criteria, '_column');
$operator = strtoupper(trim((string) $this->getItemCriteriaProperty($criteria, '_operator')));
$value = $this->getItemCriteriaProperty($criteria, '_value');

if (!in_array($column, $this->supportedCriteriaFields, true)) {
$unsupported[] = $column;
return true;
}

$fileValue = $fileObject->getVar($column, 'n');
if (in_array($column, array('size', 'mtime'), true)) {
$fileValue = (int) $fileValue;
}

switch ($operator) {
case '!=':
case '<>':
return $fileValue != $value;
case '>':
return $fileValue > $value;
case '>=':
return $fileValue >= $value;
case '<':
return $fileValue < $value;
case '<=':
return $fileValue <= $value;
case 'LIKE':
return $this->matchesLike($fileValue, $value);
case 'IN':
return in_array((string) $fileValue, $this->normalizeSetValues($value), true);
case 'NOT IN':
return !in_array((string) $fileValue, $this->normalizeSetValues($value), true);
case 'IS NULL':
return $fileValue === '' || $fileValue === null;
case 'IS NOT NULL':
return !($fileValue === '' || $fileValue === null);
case '=':
default:
return $fileValue == $value;
}
}

protected function getItemCriteriaProperty($criteria, $property) {
$reflection = new ReflectionClass($criteria);
$prop = $reflection->getProperty($property);
$prop->setAccessible(true);
return $prop->getValue($criteria);
}

protected function matchesLike($fileValue, $pattern) {
$quoted = preg_quote((string) $pattern, '/');
$regex = '/^' . str_replace(array('%', '_'), array('.*', '.'), $quoted) . '$/i';
return (bool) preg_match($regex, (string) $fileValue);
}

protected function normalizeSetValues($value) {
if (is_array($value)) {
return array_map('strval', $value);
}

$value = trim((string) $value);
$value = trim($value, '()');
if ($value === '') {
return array();
}

$parts = array_map('trim', explode(',', $value));
return array_map(
function ($item) {
$item = trim($item);
if ((substr($item, 0, 1) === '"' && substr($item, -1) === '"') || (substr($item, 0, 1) === '\'' && substr($item, -1) === '\'')) {
$item = substr($item, 1, -1);
}
return $item;
},
$parts
);
}

protected function sortObjects(array &$objects, $sort, $order) {
if (!in_array($sort, array('basename', 'extension', 'size', 'mtime', 'path'), true)) {
$sort = 'basename';
}

$order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
usort(
$objects,
function ($a, $b) use ($sort, $order) {
$left = $a->getVar($sort, 'n');
$right = $b->getVar($sort, 'n');
if (in_array($sort, array('size', 'mtime'), true)) {
$cmp = ((int) $left <=> (int) $right);
} else {
$cmp = strnatcasecmp((string) $left, (string) $right);
}
return $order === 'DESC' ? -$cmp : $cmp;
}
);
}

protected function applyRange(array $objects, $criteria) {
if (!($criteria instanceof icms_db_criteria_Element)) {
return $objects;
}

$start = (int) $criteria->getStart();
$limit = (int) $criteria->getLimit();

if ($start > 0 || $limit > 0) {
$objects = array_slice($objects, $start, $limit > 0 ? $limit : null);
}

return $objects;
}
}
