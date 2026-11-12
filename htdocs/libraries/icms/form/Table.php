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
// Author: Kazumi Ono (AKA onokazu)                                          //
// URL: http://www.myweb.ne.jp/, http://www.xoops.org/, http://jp.xoops.org/ //
// Project: The XOOPS Project                                                //
// ------------------------------------------------------------------------- //
/**
 * Creates a form styled by a table
 *
 * @copyright	http://www.impresscms.org/ The ImpressCMS Project
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)

 * @package		Form
 * @version		SVN: $Id: Table.php 12313 2013-09-15 21:14:35Z skenow $
 */

defined('ICMS_ROOT_PATH') or die('ImpressCMS root path not defined');

/**
 * Form that will output formatted as a HTML table
 *
 * No styles and no JavaScript to check for required fields.
 *
 * @category	ICMS
 * @package     Form
 *
 * @author		Kazumi Ono	<onokazu@xoops.org>
 * @copyright	copyright (c) 2000-2003 XOOPS.org
 */
class icms_form_Table extends icms_form_Base {
	/**
	 * Insert an empty row in the table to serve as a separator.
	 *
	 * @param	string  $extra  HTML to be displayed in the empty row.
	 * @param	string	$class	CSS class name for <td> tag
	 */
	public function insertBreak($extra = '', $class= '') {
		$row = $this->renderBreakRow($extra, $class);
		$this->addElement($row);
	}

	/**
	 * create HTML to output the form as a table
	 *
	 * @return	string  $ret  the constructed HTML
	 */
	public function render() {
		$elements = array();
		$hidden = '';
		foreach ($this->getElements() as $ele) {
			if (!is_object($ele)) {
				$elements[] = array('raw' => $ele);
			} elseif (!$ele->isHidden()) {
				$elements[] = array(
					'caption' => $ele->getCaption(),
					'description' => $ele->getDescription(),
					'html' => $ele->render()
				);
			} else {
				$hidden .= $ele->render() . "\n";
			}
		}
		return $this->renderTemplate('icms_form_table.html', array(
			'title' => $this->getTitle(),
			'name' => $this->getName(),
			'action' => $this->getAction(),
			'method' => $this->getMethod(),
			'extra' => $this->getExtra(),
			'align' => _GLOBAL_LEFT,
			'elements' => $elements,
			'hidden' => $hidden
		));
	}
}
