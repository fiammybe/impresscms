<?php
/**
 * Image Creation class for CAPTCHA
 * Xoops Frameworks addon
 *
 * based on Frameworks::captcha by Taiwen Jiang (phppp or D.J.) <php_pp@hotmail.com>
 *
 * @copyright	The XOOPS project http://www.xoops.org/
 * @license 	http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author		Taiwen Jiang (phppp or D.J.) <php_pp@hotmail.com>
 * @since		XOOPS
 *
 * @category	ICMS
 * @package		Form
 * @subpackage	Elements
 * @author		Taiwen Jiang (phppp or D.J.) <php_pp@hotmail.com>
 * @version		SVN: $Id: Image.php 12340 2013-09-22 04:11:09Z skenow $
 */

namespace Icms\Form\Elements\Captcha;

/**
 * The captcha image
 *
 * @author		modified by Sina Asghari (aka stranger) <pesian_stranger@users.sourceforge.net>
 * @category	ICMS
 * @package		Form
 * @subpackage	Elements
 *
 */
class Image {
	//var $config	= array();

	/**
	 * Constructor
	 */
	public function __construct() {
	}

	/**
	 * Creates instance of icmsCaptchaImage
	 * @return  object the icms_form_elements_captcha_Image object
	 */
	public static function &instance() {
		static $instance;
		if (!isset($instance)) {
			$instance = new self();
		}
		return $instance;
	}

	/**
	 * Loading configs from CAPTCHA class
	 * @param   array $config the configuration array
	 */
	public function loadConfig($config = array()) {
		// Loading default preferences
		$this->config =& $config;
	}

	/**
	 * Renders the Captcha image Returns form with image in it
	 * @return  string String that contains the Captcha Image form
	 */
	public function render() {
		return \Icms\View\SystemTemplate::fetch('icms_form_elements_captcha_image_display.html', [
			'field' => $this->getDefinition()->toArray(),
			'view' => $this->viewData(),
		]);
	}

	/**
	 * The definition: a compound field of type "captcha" (mode "image") with
	 * the text input for the code as child "code"
	 */
	public function getDefinition(): \Icms\Form\Definition\CompoundField {
		global $icmsConfigCaptcha;
		$name = (string) $this->config["name"];
		$chars = (int) $icmsConfigCaptcha['captcha_num_chars'];
		return new \Icms\Form\Definition\CompoundField($name, 'captcha', children: [
			'code' => new \Icms\Form\Definition\Field($name, 'text', value: '', required: true, attributes: ['size' => $chars, 'maxlength' => $chars], constraints: ['maxlength' => $chars]),
		], required: true, attributes: [
			'mode' => 'image',
			'image_url' => ICMS_URL . '/libraries/Icms/Form/Elements/Captcha/img.php',
			'case_sensitive' => (bool) $icmsConfigCaptcha['captcha_casesensitive'],
			'max_attempts' => (int) $icmsConfigCaptcha['captcha_maxattempt'],
		]);
	}

	/**
	 * Texts for the template: rule (alt text of the image) and hint
	 *
	 * @return array{rule: string, hint: string}
	 */
	private function viewData(): array {
		global $icmsConfigCaptcha;
		$hint = htmlspecialchars(ICMS_CAPTCHA_REFRESH, ENT_QUOTES);
		if ($icmsConfigCaptcha['captcha_maxattempt']) {
			$hint .= " | " . sprintf(constant("ICMS_CAPTCHA_MAXATTEMPTS"), $icmsConfigCaptcha['captcha_maxattempt']);
		}
		return [
			'rule' => $icmsConfigCaptcha['captcha_casesensitive'] ? constant("ICMS_CAPTCHA_RULE_CASESENSITIVE") : constant("ICMS_CAPTCHA_RULE_CASEINSENSITIVE"),
			'hint' => $hint,
		];
	}

	/**
	 * The captcha image tag
	 *
	 * @deprecated	the image is part of icms_form_elements_captcha_image_display.html; use render()
	 * @return  string String that contains the Captcha image
	 */
	public function loadImage() {
		$field = $this->getDefinition();
		$url = $field->attribute('image_url');
		return "<img id='captcha' src='" . $url . "' onclick=\"this.src='" . $url . "?refresh='+Math.random()"
			. "\" style='cursor: pointer;margin-left: auto;margin-right: auto;text-align:center;' alt='" . htmlspecialchars($this->viewData()['rule'], ENT_QUOTES) . "' />";
	}
}
