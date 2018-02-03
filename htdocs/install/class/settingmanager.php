<?php
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //

/**
 * Settings manager Class
 *
 * @copyright	http://www.xoops.org/ The XOOPS Project
 * @copyright	XOOPS_copyrights.txt
 * @copyright	http://www.impresscms.org/ The ImpressCMS Project
 * @license	http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package	installer
 * @since	XOOPS
 * @author	http://www.xoops.org The XOOPS Project
 * @author	modified by UnderDog <underdog@impresscms.org>
 * @version	$Id: settingmanager.php 12329 2013-09-19 13:53:36Z skenow $
 */

/**
 * setting manager for XOOPS installer
 *
 * @author Haruki Setoyama  <haruki@planewave.org>
 * @version $Id: settingmanager.php 12329 2013-09-19 13:53:36Z skenow $
 * @access public
 **/
class setting_manager {

	var $database;
	var $dbhost;
	var $dbuname;
	var $dbpass;
	var $dbname;
	var $prefix;
	var $db_pconnect;
	var $xoops_url;

	var $sanitizer;

	function setting_manager($post=false) {

		$this->sanitizer =& icms_core_Textsanitizer::getInstance();
		if ($post) {
			$this->readPost();
		} else {
			$this->database = 'mysql';
			$this->dbhost = 'localhost';

			$this->db_pconnect = 0;

			$filepath = (! empty($_SERVER['REQUEST_URI']))
			? dirname($_SERVER['REQUEST_URI'])
			: dirname($_SERVER['SCRIPT_NAME']);

			$filepath = str_replace("\\", "/", $filepath); // "
			$filepath = str_replace("/install", "", $filepath);
			if (substr($filepath, 0, 1) == "/") {
				$filepath = substr($filepath,1);
			}
			if (substr($filepath, -1) == "/") {
				$filepath = substr($filepath, 0, -1);
			}
			$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
			$this->xoops_url = (!empty($filepath)) ? $protocol.$_SERVER['HTTP_HOST']."/".$filepath : $protocol.$_SERVER['HTTP_HOST'];

			$this->prefix = $this->generatePrefix();
		}
	}

	function readPost() {
		if (isset($_POST['database']))
		$this->database = $this->sanitizer->stripSlashesGPC($_POST['database']);
		if (isset($_POST['dbhost']))
		$this->dbhost = $this->sanitizer->stripSlashesGPC($_POST['dbhost']);
		if (isset($_POST['dbuname']))
		$this->dbuname = $this->sanitizer->stripSlashesGPC($_POST['dbuname']);
		if (isset($_POST['dbpass']))
		$this->dbpass = $this->sanitizer->stripSlashesGPC($_POST['dbpass']);
		if (isset($_POST['dbname']))
		$this->dbname = $this->sanitizer->stripSlashesGPC($_POST['dbname']);
		if (isset($_POST['prefix']))
		$this->prefix = $this->sanitizer->stripSlashesGPC($_POST['prefix']);
		if (isset($_POST['db_pconnect']))
		$this->db_pconnect = (int) ($_POST['db_pconnect']) > 0 ? 1 : 0;
		if (isset($_POST['xoops_url']))
		$this->xoops_url = $this->sanitizer->stripSlashesGPC($_POST['xoops_url']);
	}

	function readConstant() {
		$env = new \Dotenv\Dotenv(
			dirname(
				dirname(__DIR__)
			)
		);
		$env->safeLoad();
		$this->database = getenv('DB_TYPE');
		$this->dbhost = getenv('DB_HOST');
		$this->dbuname = getenv('DB_USER');
		$this->dbpass = getenv('DB_PASS');
		$this->dbname = getenv('DB_NAME');
		$this->prefix = getenv('DB_PREFIX');
		$this->db_pconnect = ((int) getenv('DB_PCONNECT')) > 0 ? 1 : 0;
		$this->xoops_url = getenv('URL');
	}

	function checkData() {
		$ret = '';
		$error = array();

		if (empty($this->dbhost)) {
			$error[] = sprintf(_INSTALL_L57, _INSTALL_L27);
		}
		if (empty($this->dbname)) {
			$error[] = sprintf(_INSTALL_L57, _INSTALL_L29);
		}
		if (empty($this->prefix)) {
			$error[] = sprintf(_INSTALL_L57, _INSTALL_L30);
		}
		if (empty($this->xoops_url)) {
			$error[] = sprintf(_INSTALL_L57, _INSTALL_L56);
		}

		if (!empty($error)) {
			foreach ( $error as $err) {
				$ret .=  "<p><span style='color:#ff0000;'><b>".$err."</b></span></p>\n";
			}
		}

		return $ret;
	}

	function editform() {
		$ret =
            "<table width='100%' class='outer' cellspacing='5'>
                <tr>
                    <th colspan='2'></th>
                </tr>
                <tr valign='top' align='left'>
                    <td class='head'>
                        <b>"._INSTALL_L51."</b><br />
                        <span style='font-size:85%;'>"._INSTALL_L66."</span>
                    </td>
                    <td class='even'>
                        <select  size='1' name='database' id='database'>";
		$dblist = $this->getDBList();
		foreach ($dblist as $val) {
			$ret .= "<option value='$val'";
			if ($val == $this->database) $ret .= " selected='selected'";
			$ret .= "'>$val</option>";
		}
		$ret .=         "</select>
                    </td>
                </tr>
                ";
		$ret .= $this->editform_sub(_INSTALL_L27, _INSTALL_L67, 'dbhost', $this->sanitizer->htmlSpecialChars($this->dbhost));
		$ret .= $this->editform_sub(_INSTALL_L28, _INSTALL_L65, 'dbuname', $this->sanitizer->htmlSpecialChars($this->dbuname));
		$ret .= $this->editform_sub(_INSTALL_L52, _INSTALL_L68, 'dbpass', $this->sanitizer->htmlSpecialChars($this->dbpass));
		$ret .= $this->editform_sub(_INSTALL_L29, _INSTALL_L64, 'dbname', $this->sanitizer->htmlSpecialChars($this->dbname));
		$ret .= $this->editform_sub(_INSTALL_L30, _INSTALL_L63, 'prefix', $this->sanitizer->htmlSpecialChars($this->prefix));

		$ret .= "<tr valign='top' align='left'>
                    <td class='head'>
                        <b>"._INSTALL_L54."</b><br />
                        <span style='font-size:85%;'>"._INSTALL_L69."</span>
                    </td>
                    <td class='even'>
                        <input type='radio' name='db_pconnect' value='1'".($this->db_pconnect == 1 ? " checked='checked'" : "" )." />"._INSTALL_L23."
                        <input type='radio' name='db_pconnect' value='0'".($this->db_pconnect != 1 ? " checked='checked'" : "" )." />"._INSTALL_L24."
                    </td>
                </tr>
                ";

		$ret .= $this->editform_sub(_INSTALL_L55, _INSTALL_L59, 'root_path', $this->sanitizer->htmlSpecialChars($this->root_path));
		$ret .= $this->editform_sub(_INSTALL_L56, _INSTALL_L58, 'xoops_url', $this->sanitizer->htmlSpecialChars($this->xoops_url));

		$ret .= "</table>";
		return $ret;
	}

	function editform_sub($title, $desc, $name, $value) {
		$inputType = 'text';
		return  "<tr valign='top' align='left'>
                    <td class='head'>
                        <b>".$title."</b><br />
                        <span style='font-size:85%;'>".$desc."</span>
                    </td>
                    <td class='even'>
                        <input type='$inputType' name='".$name."' id='".$name."' size='30' maxlength='100' value='".htmlspecialchars($value)."' />
                    </td>
                </tr>
                ";
	}

	function confirmForm() {
		$yesno = empty($this->db_pconnect) ? _INSTALL_L24 : _INSTALL_L23;
		$ret =
            "<table border='0' cellpadding='0' cellspacing='0' valign='top' width='90%'><tr><td class='bg2'>
                <table width='100%' border='0' cellpadding='4' cellspacing='1'>
                    <tr>
                        <td class='bg3'><b>"._INSTALL_L51."</b></td>
                        <td class='bg1'>".$this->sanitizer->htmlSpecialChars($this->database)."</td>
                    </tr>
                    <tr>
                        <td class='bg3'><b>"._INSTALL_L27."</b></td>
                        <td class='bg1'>".$this->sanitizer->htmlSpecialChars($this->dbhost)."</td>
                    </tr>
                    <tr>
                        <td class='bg3'><b>"._INSTALL_L28."</b></td>
                        <td class='bg1'>".$this->sanitizer->htmlSpecialChars($this->dbuname)."</td>
                    </tr>
                    <tr>
                        <td class='bg3'><b>"._INSTALL_L52."</b></td>
                        <td class='bg1'>".$this->sanitizer->htmlSpecialChars($this->dbpass)."</td>
                    </tr>
                    <tr>
                        <td class='bg3'><b>"._INSTALL_L29."</b></td>
                        <td class='bg1'>".$this->sanitizer->htmlSpecialChars($this->dbname)."</td>
                    </tr>
                    <tr>
                        <td class='bg3'><b>"._INSTALL_L30."</b></td>
                        <td class='bg1'>".$this->sanitizer->htmlSpecialChars($this->prefix)."</td>
                    </tr>
                    <tr>
                        <td class='bg3'><b>"._INSTALL_L54."</b></td>
                        <td class='bg1'>".$yesno."</td>
                    </tr>
                    <tr>
                        <td class='bg3'><b>"._INSTALL_L56."</b></td>
                        <td class='bg1'>".$this->sanitizer->htmlSpecialChars($this->xoops_url)."</td>
                    </tr>
                </table></td></tr>
            </table>
            <input type='hidden' name='database' value='".$this->sanitizer->htmlSpecialChars($this->database)."' />
            <input type='hidden' name='dbhost' value='".$this->sanitizer->htmlSpecialChars($this->dbhost)."' />
            <input type='hidden' name='dbuname' value='".$this->sanitizer->htmlSpecialChars($this->dbuname)."' />
            <input type='hidden' name='dbpass' value='".$this->sanitizer->htmlSpecialChars($this->dbpass)."' />
            <input type='hidden' name='dbname' value='".$this->sanitizer->htmlSpecialChars($this->dbname)."' />
            <input type='hidden' name='prefix' value='".$this->sanitizer->htmlSpecialChars($this->prefix)."' />
            <input type='hidden' name='db_pconnect' value='". ((int) ($this->db_pconnect))."' />
            <input type='hidden' name='xoops_url' value='".$this->sanitizer->htmlSpecialChars($this->xoops_url)."' />
            ";
		return $ret;
	}

	function getDBList()
	{
		$dirname = '../class/database/drivers';
		$list = icms_core_Filesystem::getDirList($dirname);
		// TODO: Add any verification of compatibility between the database drivers and the system.
		return $list;
	}

	function generatePrefix() {
		include_once dirname(dirname(__DIR__))."/include/functions.php";
		return xoops_makepass(5);
	}
}