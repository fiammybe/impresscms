<?php
include_once dirname(__DIR__, 2) . '/mainfile.php';

header('Content-Type: application/json; charset=utf-8');

$response = function ($uploaded, $message = '', $fileName = '', $url = '') {
	$result = array('uploaded' => $uploaded ? 1 : 0);
	if ($fileName !== '') {
		$result['fileName'] = $fileName;
	}
	if ($url !== '') {
		$result['url'] = $url;
	}
	if (!$uploaded) {
		$result['error'] = array('message' => ($message !== '' ? $message : _ER_UP_UNKNOWNFILETYPEREJECTED));
		http_response_code(400);
	}
	echo json_encode($result);
	exit;
};

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	$response(0, _NOPERM);
}

if (!is_object(icms::$user)) {
	$response(0, _NOPERM);
}

$moduleName = isset($_REQUEST['module']) ? $_REQUEST['module'] : '';
$objectType = isset($_REQUEST['objecttype']) ? $_REQUEST['objecttype'] : '';

$moduleName = preg_replace('/[^a-z0-9_-]/i', '', $moduleName);
if ($moduleName === '' && is_object(icms::$module)) {
	$moduleName = icms::$module->getVar('dirname');
}
$moduleName = ($moduleName !== '') ? $moduleName : 'system';
$objectType = preg_replace('/[^a-z0-9_-]/i', '', $objectType);
$objectType = ($objectType !== '') ? $objectType : 'content';

$token = isset($_SERVER['HTTP_X_ICMS_TOKEN']) ? $_SERVER['HTTP_X_ICMS_TOKEN'] : '';
if ($token === '' && isset($_REQUEST[_CORE_TOKEN . '_REQUEST'])) {
	$token = $_REQUEST[_CORE_TOKEN . '_REQUEST'];
}

if ($token === '' || !icms::$security->validateToken($token)) {
	$response(0, _CORE_TOKENINVALID);
}

$moduleHandler = icms::handler('icms_module');
$module = $moduleHandler->getByDirname($moduleName);
if (!is_object($module) || !$module->getVar('isactive')) {
	$response(0, _NOPERM);
}

$groups = icms::$user->getGroups();
$grouppermHandler = icms::handler('icms_member_groupperm');
if (!$grouppermHandler->checkRight('module_read', $module->getVar('mid'), $groups, 1) && !icms::$user->isAdmin($module->getVar('mid'))) {
	$response(0, _NOPERM);
}

$allowedFields = array('upload', 'file');
$providedFields = array();
foreach ($allowedFields as $field) {
	if (!empty($_FILES[$field]['name'])) {
		$providedFields[] = $field;
	}
}
if (empty($providedFields)) {
	$response(0, _ER_UP_NOFILEUPLOADED);
}
if (count($providedFields) > 1) {
	$response(0, 'Only one file upload is allowed per request');
}
$uploadField = $providedFields[0];

$destination = ICMS_UPLOAD_PATH . '/' . $moduleName . '/' . $objectType;

$allowedTypes = array(
	'jpg' => array('image/jpeg', 'image/pjpeg'),
	'jpeg' => array('image/jpeg', 'image/pjpeg'),
	'png' => array('image/png', 'image/x-png'),
	'gif' => array('image/gif'),
	'webp' => array('image/webp')
);
$allowedExtensions = array_keys($allowedTypes);
$allowedMimeTypes = array();
foreach ($allowedTypes as $mimeList) {
	$allowedMimeTypes = array_merge($allowedMimeTypes, $mimeList);
}
$allowedMimeTypes = array_unique($allowedMimeTypes);

$fileInfo = $_FILES[$uploadField];

$maxFileSize = isset(icms::$config['maxfilesize']) ? (int) icms::$config['maxfilesize'] : 0;
if ($maxFileSize <= 0) {
	$size = ini_get('upload_max_filesize');
	$unit = strtolower(substr($size, -1));
	$value = (int) $size;
	switch ($unit) {
		case 'g':
			$value *= 1024;
			// no break
		case 'm':
			$value *= 1024;
			// no break
		case 'k':
			$value *= 1024;
	}
	$maxFileSize = $value;
}

if (!icms_core_Filesystem::mkdir($destination, 0750, '') && !is_dir($destination)) {
	$response(0, _ER_UP_FAILEDOPENDIRWRITE);
}

$uploader = new icms_file_MediaUploadHandler($destination, $allowedMimeTypes, $maxFileSize, null, null);
$basename = preg_replace('/[^a-z0-9_-]/i', '', pathinfo($fileInfo['name'], PATHINFO_FILENAME));
if ($basename === '') {
	$basename = 'image';
}
$extension = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
if ($extension === '' || !in_array($extension, $allowedExtensions)) {
	$response(0, _ER_UP_UNKNOWNFILETYPEREJECTED);
}
try {
	$uniqueSuffix = bin2hex(random_bytes(8));
} catch (Exception $e) {
	$response(0, _ER_UP_INVALIDFILENAME);
}
$uniqueName = $basename . '-' . $uniqueSuffix . '.' . $extension;
$uploader->setTargetFileName($uniqueName);

if (!$uploader->fetchMedia($uploadField)) {
	$response(0, implode('; ', $uploader->getErrors(false)));
}

if (!$uploader->upload()) {
	$response(0, implode('; ', $uploader->getErrors(false)));
}

$fileUrl = ICMS_UPLOAD_URL . '/' . $moduleName . '/' . $objectType . '/' . rawurlencode($uploader->getSavedFileName());
$response(1, '', $uploader->getSavedFileName(), $fileUrl);
