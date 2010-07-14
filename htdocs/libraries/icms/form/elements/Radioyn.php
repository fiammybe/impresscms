<?php
/**
 * Creates a form radiobutton attribute
 *
 * @copyright	http://www.xoops.org/ The XOOPS Project
 * @copyright	XOOPS_copyrights.txt
 * @copyright	http://www.impresscms.org/ The ImpressCMS Project
 * @license	LICENSE.txt
 * @package	XoopsForms
 * @since	XOOPS
 * @author	http://www.xoops.org The XOOPS Project
 * @author	modified by UnderDog <underdog@impresscms.org>
 * @version	$Id: formradioyn.php 19118 2010-03-27 17:46:23Z skenow $
 */

if (!defined('ICMS_ROOT_PATH')) die("ImpressCMS root path not defined");

/**
 * @package     kernel
 * @subpackage  form
 *
 * @author	    Kazumi Ono	<onokazu@xoops.org>
 * @copyright	copyright (c) 2000-2003 XOOPS.org
 */

/**
 * Yes/No radio buttons.
 *
 * A pair of radio buttons labeled _YES and _NO with values 1 and 0
 *
 * @package     kernel
 * @subpackage  form
 *
 * @author	    Kazumi Ono	<onokazu@xoops.org>
 * @copyright	copyright (c) 2000-2003 XOOPS.org
 */
class icms_form_elements_Radioyn extends icms_form_elements_Radio
{
	/**
	 * Constructor
	 *
	 * @param	string	$caption
	 * @param	string	$name
	 * @param	string	$value		Pre-selected value, can be "0" (No) or "1" (Yes)
	 * @param	string	$yes		String for "Yes"
	 * @param	string	$no			String for "No"
	 */
	function icms_form_elements_Radioyn($caption, $name, $value = null, $yes = _YES, $no = _NO)
	{
		$this->icms_form_elements_Radio($caption, $name, $value);
		$this->addOption(1, '&nbsp;' . $yes . '&nbsp;');
		$this->addOption(0, '&nbsp;' . $no);
	}
}

?>