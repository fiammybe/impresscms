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
 * Creates a form attribute which is able to select a language
 *
 * @copyright	http://www.impresscms.org/ The ImpressCMS Project
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 *
 * @category	ICMS
 * @package		Form
 * @subpackage	Elements
 * @version	$Id: Lang.php 12313 2013-09-15 21:14:35Z skenow $
 */
defined('ICMS_ROOT_PATH') or die("ImpressCMS root path not defined");

/**
 * A specialized select form element for selecting languages
 *
 * The Language select class creates a form element that allows users to select a language
 * from all available languages installed in the ImpressCMS system. It automatically discovers
 * and populates with all language directories found in the /language/ folder.
 *
 * ## Class Overview
 *
 * This class extends icms_form_elements_Select and provides specialized functionality for:
 * - Displaying all available installed languages
 * - Automatically discovering languages from the /language/ directory
 * - Supporting single language selection
 * - Using language directory names as values
 * - Providing language names as display labels
 *
 * ## When to Use
 *
 * Use this class when you need to:
 * - Allow users to select their preferred language
 * - Set system-wide language preferences
 * - Configure module language settings
 * - Implement language switching functionality
 * - Store language preferences in user profiles
 * - Set default language for content
 * - Support multi-language administration
 * - Configure language-specific settings
 *
 * ## Key Features
 *
 * - **Automatic Discovery**: Languages automatically discovered from /language/ directory
 * - **Dynamic Population**: List updates when new languages are added
 * - **Directory-Based**: Uses language directory names as values
 * - **Filesystem Integration**: Uses icms_core_Filesystem for directory listing
 * - **Single Selection**: Supports single language selection
 * - **Dropdown Format**: Renders as dropdown by default
 *
 * ## Inheritance
 *
 * This class extends icms_form_elements_Select and inherits all its functionality
 * while adding specialized language selection capabilities.
 *
 * @category	ICMS
 * @package     Form
 * @subpackage  Elements
 * @author	    Kazumi Ono	<onokazu@xoops.org>
 * @copyright	copyright (c) 2000-2003 XOOPS.org
 *
 * @example
 * // Basic language selection (dropdown)
 * $langSelect = new icms_form_elements_select_Lang('Select Language', 'language');
 * $form->addElement($langSelect);
 *
 * @example
 * // Language selection with pre-selected value
 * $langSelect = new icms_form_elements_select_Lang('Language', 'language', 'english');
 * $form->addElement($langSelect);
 *
 * @example
 * // Language selection with custom size
 * $langSelect = new icms_form_elements_select_Lang('Language', 'language', null, 5);
 * $form->addElement($langSelect);
 *
 * @example
 * // Language selection in user preferences
 * $langSelect = new icms_form_elements_select_Lang(
 *     'Preferred Language',
 *     'user_language',
 *     $user->getVar('language')
 * );
 * $form->addElement($langSelect);
 */
class icms_form_elements_select_Lang extends icms_form_elements_Select {
	/**
	 * Constructor
	 *
	 * Creates a new Language select form element with automatic language discovery from
	 * the /language/ directory. All available language directories are automatically loaded
	 * and populated as options.
	 *
	 * ## Behavior
	 *
	 * The constructor automatically:
	 * 1. Calls the parent Select constructor with provided parameters
	 * 2. Scans the /language/ directory for subdirectories
	 * 3. Uses icms_core_Filesystem::getDirList() to discover languages
	 * 4. Populates the select element with discovered language directories
	 * 5. Uses directory names as both values and display labels
	 *
	 * ## Language Directory Structure
	 *
	 * Languages are discovered from: {ICMS_ROOT_PATH}/language/
	 *
	 * Example directory structure:
	 * ```
	 * /language/
	 *   /english/
	 *   /french/
	 *   /german/
	 *   /spanish/
	 *   /chinese/
	 * ```
	 *
	 * Each directory name becomes both the value and label in the select element.
	 *
	 * @param string $caption The label/caption displayed for this form element.
	 *                        Example: "Select Language", "Preferred Language"
	 *
	 * @param string $name The HTML "name" attribute for the select element.
	 *                     Example: "language", "user_language", "site_language"
	 *
	 * @param mixed $value Optional. Pre-selected language directory name(s). Can be:
	 *                     - null: No language pre-selected (default)
	 *                     - string: Single language directory name (e.g., 'english', 'french')
	 *                     - array: Multiple language names (e.g., array('english', 'french'))
	 *                     Example: 'english' or array('english', 'french')
	 *
	 * @param int $size Number of visible rows in the select element.
	 *                  - 1: Renders as a dropdown list (default)
	 *                  - >1: Renders as a multi-row list box
	 *                  Example: 1 or 5
	 *
	 * @return void
	 *
	 * @example
	 * // Basic language selection (dropdown)
	 * $select = new icms_form_elements_select_Lang('Select Language', 'language');
	 *
	 * @example
	 * // Language selection with pre-selected value
	 * $select = new icms_form_elements_select_Lang('Language', 'language', 'english');
	 *
	 * @example
	 * // Language selection with custom size
	 * $select = new icms_form_elements_select_Lang('Language', 'language', null, 5);
	 *
	 * @example
	 * // Language selection in user preferences
	 * $select = new icms_form_elements_select_Lang(
	 *     'Preferred Language',
	 *     'user_language',
	 *     $user->getVar('language')
	 * );
	 */
	public function __construct($caption, $name, $value = null, $size = 1) {
		parent::__construct($caption, $name, $value, $size);
		$this->addOptionArray(icms_core_Filesystem::getDirList(ICMS_ROOT_PATH."/language/"));
	}
}
