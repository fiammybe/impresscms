<?php
// ------------------------------------------------------------------------ //
// XOOPS - PHP Content Management System                                   //
// Copyright (c) 2000 XOOPS.org                                            //
// <http://www.xoops.org/>                                                 //
// ------------------------------------------------------------------------ //
// This program is free software; you can redistribute it and/or modify    //
// it under the terms of the GNU General Public License as published by    //
// the Free Software Foundation; either version 2 of the License, or       //
// (at your option) any later version.                                     //
//                                                                          //
// You may not change or alter any portion of this comment or credits      //
// of supporting developers from this source code or any supporting        //
// source code which is considered copyrighted (c) material of the         //
// original comment or credit authors.                                     //
//                                                                          //
// This program is distributed in the hope that it will be useful,          //
// but WITHOUT ANY WARRANTY; without even the implied warranty of           //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
// GNU General Public License for more details.                            //
//                                                                          //
// You should have received a copy of the GNU General Public License        //
// along with this program; if not, write to the Free Software             //
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
// ------------------------------------------------------------------------ //

if (!defined('ICMS_ROOT_PATH')) {
	die("ImpressCMS root path not defined");
}

/**
 * Automatic upgrades to remove XML-RPC support
 */

// Remove XML-RPC related files and directories
$removeXmlRpcFiles = [
	ICMS_ROOT_PATH . '/xmlrpc.php',
	ICMS_ROOT_PATH . '/libraries/xml/rpc/bloggerapi.php',
	ICMS_ROOT_PATH . '/libraries/xml/rpc/metaweblogapi.php',
	ICMS_ROOT_PATH . '/libraries/xml/rpc/movabletypeapi.php',
	ICMS_ROOT_PATH . '/libraries/xml/rpc/xoopsapi.php',
	ICMS_ROOT_PATH . '/libraries/xml/rpc/xmlrpcparser.php',
	ICMS_ROOT_PATH . '/libraries/xml/rpc/xmlrpctag.php'
];

foreach ($removeXmlRpcFiles as $fileToRemove) {
	if (file_exists($fileToRemove)) {
		echo icms_core_Filesystem::deleteFile($fileToRemove);
		echo 'Removed: ' . basename($fileToRemove) . '<br />';
	} else {
		echo "Warning: File not found for removal: " . basename($fileToRemove) . "<br />";
	}
}

// Remove the empty xml/rpc directory if it exists
$xmlRpcDir = ICMS_LIBRARIES_PATH . '/xml/rpc';
if (is_dir($xmlRpcDir)) {
	echo icms_core_Filesystem::deleteRecursive($xmlRpcDir, true);
	echo 'Removed: directories/xml/rpc/<br />';
}

// Remove xmlrpc.php if it exists in root
$xmlRpcRoot = ICMS_ROOT_PATH . '/xmlrpc.php';
if (file_exists($xmlRpcRoot)) {
	echo icms_core_Filesystem::deleteFile($xmlRpcRoot);
	echo 'Removed: xmlrpc.php<br />';
}

echo "XML-RPC support has been removed.<br />";
?>
