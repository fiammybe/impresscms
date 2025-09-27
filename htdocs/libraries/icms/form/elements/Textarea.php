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
 * Creates a textarea form attribut
 *
 * @copyright	http://www.impresscms.org/ The ImpressCMS Project
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)

 * @category	ICMS
 * @package		Form
 * @subpackage	Elements
 * @version		SVN: $Id: Textarea.php 12313 2013-09-15 21:14:35Z skenow $
 */
defined('ICMS_ROOT_PATH') or die("ImpressCMS root path not defined");

/**
 * A textarea with extensible content type handling (plaintext, HTML, Markdown)
 *
 * Backward compatibility: default behavior and constructor remain compatible; output
 * of render() is unchanged unless optional content-type selector is explicitly used.
 *
 * @category	ICMS
 * @package     Form
 * @subpackage  Elements
 *
 * @author		Kazumi Ono	<onokazu@xoops.org>
 * @copyright	copyright (c) 2000-2003 XOOPS.org
 */
class icms_form_elements_Textarea extends icms_form_Element {
	/** @var int */
	protected $_cols;
	/** @var int */
	protected $_rows;
	/** @var string */
	protected $_value;
	/** @var string|null Content type identifier: 'plaintext'|'html'|'markdown' */
	protected $_contentType = null;
	/** @var array Additional options */
	protected $_options = array();

	/**
	 * Constructor
	 *
	 * @param string $caption caption
	 * @param string $name name
	 * @param string $value initial content
	 * @param int    $rows number of rows
	 * @param int    $cols number of columns
	 * @param array  $options Optional: ['content_type' => 'plaintext|html|markdown']
	 */
	public function __construct($caption, $name, $value = "", $rows = 5, $cols = 50, $options = array()) {
		$this->setCaption($caption);
		$this->setName($name);
		$this->_rows = (int) $rows;
		$this->_cols = (int) $cols;
		$this->setValue($value);
		if (is_array($options)) {
			$this->_options = $options;
			if (!empty($options['content_type']) && is_string($options['content_type'])) {
				$this->setContentType($options['content_type']);
			}
		}
	}

	/**
	 * get number of rows
	 * @return int
	 */
	public function getRows() {
		return $this->_rows;
	}

	/**
	 * Get number of columns
	 * @return int
	 */
	public function getCols() {
		return $this->_cols;
	}

	/**
	 * Get initial content
	 *
	 * @param bool $encode To sanitize the text? Default value should be "true"; however we have to set "false" for backward compatibility
	 * @return string
	 */
	public function getValue($encode = false) {
		return $encode ? htmlspecialchars($this->_value, ENT_QUOTES) : $this->_value;
	}

	/**
	 * Set initial content
	 * @param string $value
	 */
	public function setValue($value){
		$this->_value = $value;
	}

	/**
	 * Set content type identifier (plaintext|html|markdown).
	 * If unknown, defaults to plaintext.
	 */
	public function setContentType(string $contentType): void {
		$this->_contentType = strtolower($contentType);
	}

	/** Get content type identifier, defaults to 'plaintext' for BC. */
	public function getContentType(): string {
		return $this->_contentType ?: 'plaintext';
	}

	/**
	 * Process arbitrary input for storage using the configured content handler.
	 * This is optional helper; legacy code may still store raw values directly.
	 */
	public function processForStorage(string $input): string {
		$handler = $this->getContentHandler();
		return $handler->processForStorage($input);
	}

	/** Render current value for output using the configured content handler. */
	public function renderValueForOutput(): string {
		$handler = $this->getContentHandler();
		// Apply sanitization for consistency and security
		return $handler->renderForOutput($this->_value ?? '', true);
	}

	/**
	 * Render an optional content type selector (not used unless called explicitly).
	 * @param string|null $selectName Alternative name for the selector; defaults to name + '_ctype'
	 */
	public function renderContentTypeSelector($selectName = null): string {
		$selectName = $selectName ?: ($this->getName(false) . '_ctype');
		$this->ensureContentTypeRuntime();
		$options = icms_form_content_ContentTypeManager::all();
		$current = $this->getContentType();
		$html = "<select name='" . htmlspecialchars($selectName, ENT_QUOTES) . "'" . $this->getExtra() . ">";
		foreach ($options as $id => $handler) {
			$sel = ($id === $current) ? " selected='selected'" : '';
			$label = htmlspecialchars($handler->getLabel(), ENT_QUOTES);
			$html .= "<option value='{$id}'{$sel}>{$label}</option>";
		}
		$html .= "</select>";
		return $html;
	}

	/** prepare HTML for output: unchanged legacy <textarea> */
	public function render(){
		return "<textarea name='" . $this->getName()
			. "' id='" . $this->getName() . '_tarea'
			. "' rows='" . $this->getRows()
			. "' cols='" . $this->getCols()
			. "'" . $this->getExtra() . ">"
			. $this->getValue()
			. "</textarea>";
	}

	/** Locate or include content type runtime classes and return the handler. */
	protected function getContentHandler() /* : icms_form_content_ContentTypeHandlerInterface */ {
		$this->ensureContentTypeRuntime();
		return icms_form_content_ContentTypeManager::get($this->getContentType());
	}

	/** Ensure manager and handlers are available without changing global autoloaders. */
	protected function ensureContentTypeRuntime(): void {
		$base = ICMS_ROOT_PATH . '/libraries/icms/form/content/';
		// Minimal includes (idempotent)
		require_once $base . 'ContentTypeHandlerInterface.php';
		require_once $base . 'AbstractContentTypeHandler.php';
		require_once $base . 'PlainTextContentTypeHandler.php';
		require_once $base . 'HtmlContentTypeHandler.php';
		require_once $base . 'MarkdownContentTypeHandler.php';
		require_once $base . 'ContentTypeManager.php';
	}
}

