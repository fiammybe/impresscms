<?php
/**
 * Mermaid.js diagram rendering preload
 *
 * This preload filter detects Mermaid diagram HTML elements in page content and
 * conditionally includes the Mermaid.js library only when needed.
 *
 * REQUIREMENTS:
 * - The Mermaid TextSanitizer plugin must be enabled in the admin panel
 *   (System > Preferences > Text Sanitizer Options)
 * - Users write diagrams using [mermaid]...[/mermaid] BBCode tags
 * - The TextSanitizer plugin transforms these to <div class="mermaid"> elements
 * - This preload detects those elements and loads the Mermaid.js library
 *
 * WORKFLOW:
 * 1. User enters: [mermaid]graph TD; A-->B[/mermaid]
 * 2. TextSanitizer plugin converts to: <div class="mermaid">graph TD; A-->B</div>
 * 3. This preload detects the <div class="mermaid"> element
 * 4. Mermaid.js library is loaded from CDN
 * 5. JavaScript initialization renders the diagram
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @category	ICMS
 * @package		libraries
 * @since		2.0
 * @author		David Janssens <fiammybe@gmail.com>
 */

/**
 * Preload event handler for Mermaid.js diagram rendering
 *
 * Detects Mermaid diagram syntax in textarea/HTML content and conditionally
 * loads the Mermaid.js library to render diagrams on pages where they are present.
 *
 * @since	2.0
 */
class IcmsPreloadMermaid extends icms_preload_Item {

	/**
	 * Flag indicating whether Mermaid content was detected on the current page
	 * @var bool
	 */
	private static $mermaidDetected = false;

	/**
	 * Regex pattern to detect Mermaid diagram HTML elements in content
	 *
	 * Matches <div class="mermaid"> elements that are created by the
	 * Mermaid TextSanitizer plugin when it processes [mermaid]...[/mermaid] BBCode tags.
	 *
	 * Note: The TextSanitizer plugin (htdocs/plugins/textsanitizer/mermaid/mermaid.php)
	 * must be enabled in the admin panel for the BBCode transformation to occur.
	 *
	 * @var string
	 */
	private static $mermaidPattern = '/<div[^>]*class=["\'][^"\']*mermaid[^"\']*["\'][^>]*>/i';

	/**
	 * Check text content for Mermaid diagram syntax
	 *
	 * @param string $text The text content to check
	 * @return bool Whether Mermaid syntax was found
	 */
	private static function containsMermaid(&$text) {
		if (self::$mermaidDetected) {
			return true;
		}
		if (!empty($text) && preg_match(self::$mermaidPattern, $text)) {
			self::$mermaidDetected = true;
			return true;
		}
		return false;
	}

	/**
	 * Event triggered after textarea content is filtered for display
	 *
	 * Scans the filtered text for Mermaid diagram syntax markers.
	 *
	 * @param array $array Parameters passed by filterTextareaDisplay()
	 *   $array[0] = &$text, $array[1] = $html, $array[2] = $smiley,
	 *   $array[3] = $xcode, $array[4] = $image, $array[5] = $br
	 * @return void
	 */
	public function eventAfterFilterTextareaDisplay($array) {
		self::containsMermaid($array[0]);
	}

	/**
	 * Event triggered after HTML content is filtered for display
	 *
	 * Scans the filtered HTML for Mermaid diagram syntax markers.
	 *
	 * @param array $array Parameters passed by filterHTMLdisplay()
	 * @return void
	 */
	public function eventAfterFilterHTMLdisplay($array) {
		self::containsMermaid($array[0]);
	}

	/**
	 * Event triggered before the page footer is rendered
	 *
	 * If Mermaid content was detected during content filtering, this method
	 * adds the Mermaid.js library from CDN and initialization script to the page.
	 * The library is only loaded on pages where Mermaid diagrams are present,
	 * avoiding unnecessary resource loading on other pages.
	 *
	 * @return void
	 */
	public function eventBeforeFooter() {
		if (!self::$mermaidDetected) {
			return;
		}

		global $xoTheme;

		if (!is_object($xoTheme)) {
			return;
		}

		// Add the Mermaid.js library from CDN
		$xoTheme->addScript(
			'https://cdn.jsdelivr.net/npm/mermaid@11/dist/mermaid.min.js',
			array(),
			'',
			'module',
			3000
		);

		// Add initialization script that renders Mermaid diagrams
		// The TextSanitizer plugin creates <div class="mermaid"> elements
		$initScript = '
document.addEventListener("DOMContentLoaded", function() {
    if (typeof mermaid !== "undefined") {
        mermaid.initialize({
            startOnLoad: true,
            theme: "default",
            securityLevel: "loose"
        });
    }
});';

		$xoTheme->addScript(
			'',
			array(),
			$initScript,
			'module',
			3001
		);
	}
}

