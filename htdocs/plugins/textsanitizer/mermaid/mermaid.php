<?php
/**
 * Mermaid.js diagram TextSanitizer plugin
 *
 * This plugin transforms [mermaid]...[/mermaid] BBCode tags into
 * <div class="mermaid">...</div> HTML elements that can be rendered
 * by the Mermaid.js library.
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @category	ICMS
 * @package		plugins
 * @subpackage	textsanitizer
 * @since		2.0
 * @author		David Janssens <fiammybe@gmail.com>
 */

/**
 * Locates and replaces [mermaid]...[/mermaid] tags with renderable HTML
 *
 * This function is called by the text sanitizer during content filtering.
 * It converts BBCode-style [mermaid] tags into <div class="mermaid"> elements
 * that the Mermaid.js library can process and render as diagrams.
 *
 * The diagram content is preserved as-is, with HTML special characters
 * properly escaped to prevent XSS attacks while maintaining the diagram syntax.
 *
 * @param string $text The text content to process
 * @return string The processed text with [mermaid] tags converted to HTML
 */
function textsanitizer_mermaid($text) {
	return preg_replace_callback("/\[mermaid](.*?)\[\/mermaid\]/s", function ($matches) {
		// Undo any HTML encoding that may have been applied to the diagram content
		$diagram = icms_core_DataFilter::undoHtmlSpecialChars($matches[1]);

		// Trim whitespace from the diagram content
		$diagram = trim($diagram);

		// Re-encode the diagram content to prevent XSS, but preserve newlines and spaces
		// Use ENT_NOQUOTES to avoid encoding quotes which are used in Mermaid syntax
		$diagram = htmlspecialchars($diagram, ENT_NOQUOTES, _CHARSET);

		// Wrap in a div with the "mermaid" class that Mermaid.js will recognize
		return '<div class="mermaid">' . $diagram . '</div>';
	}, $text);
}

/**
 * Adds button and inline script to the editor for inserting Mermaid diagrams
 *
 * This function is called when rendering the WYSIWYG editor toolbar.
 * It adds a button that allows users to easily insert [mermaid] tags
 * into their content. The JavaScript is provided inline rather than
 * loading from a separate file.
 *
 * @param string $ele_name The name of the textarea element being edited
 * @return array Array containing the button HTML and inline JavaScript
 */
function render_mermaid($ele_name) {
	$dirname = basename(dirname(__FILE__));

	// Inline JavaScript for the editor button functionality
	$javascript = '
function icmsCodeMermaid(id, enterMermaidMessage) {
	var selection = icms_getSelect(id);
	var text = selection != "" ? selection : prompt(enterMermaidMessage, "");
	if (text != null && text != "") {
		icms_insertText(id, "[mermaid]" + text + "[/mermaid]");
	}
	document.getElementById(id).focus();
}';

	// Create the toolbar button
	$code = "<img
		onclick='javascript:icmsCodeMermaid(\"" . $ele_name . "\", \"" . htmlspecialchars(_ENTERMERMAIDCODE, ENT_QUOTES, _CHARSET) . "\");'
		onmouseover='style.cursor=\"pointer\"'
		src='" . ICMS_URL . "/plugins/textsanitizer/" . $dirname . "/mermaid.png'
		alt='Mermaid'
		title='Mermaid Diagram' />&nbsp;";

	return array($code, $javascript);
}

