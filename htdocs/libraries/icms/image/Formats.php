<?php
/**
 * Detection of the optional (modern) image formats WebP and AVIF
 *
 * Whether these formats can be used depends on the PHP build: validating an upload needs
 * getimagesize() to know the format, and resizing or converting needs the matching GD functions.
 * All code that deals with these formats should ask this class instead of assuming support,
 * so a server without support simply doesn't offer the format, instead of raising errors.
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @category	ICMS
 * @package		Image
 * @since		2.0.4
 */

defined("ICMS_ROOT_PATH") or die("ImpressCMS root path not defined");

/**
 * icms_image_Formats
 *
 * @package		Image
 */
class icms_image_Formats {

	/** @var array  Optional formats: extension => mimetype */
	static private $optional = array(
		'webp' => 'image/webp',
		'avif' => 'image/avif',
	);

	/**
	 * Can an uploaded file of this format be validated? (getimagesize() has to recognise it)
	 *
	 * @param   string  $extension  'webp' or 'avif'
	 * @return  bool
	 */
	static public function canValidate($extension) {
		switch (strtolower($extension)) {
			case 'webp':
				return defined('IMAGETYPE_WEBP');
			case 'avif':
				return defined('IMAGETYPE_AVIF');
		}
		return false;
	}

	/**
	 * Can GD read and write this format? (needed for resizing and converting)
	 *
	 * @param   string  $extension  'webp' or 'avif'
	 * @return  bool
	 */
	static public function canProcess($extension) {
		if (!function_exists('imagetypes')) {
			return false;
		}
		switch (strtolower($extension)) {
			case 'webp':
				return defined('IMG_WEBP') && (imagetypes() & IMG_WEBP)
					&& function_exists('imagecreatefromwebp') && function_exists('imagewebp');
			case 'avif':
				return defined('IMG_AVIF') && (imagetypes() & IMG_AVIF)
					&& function_exists('imagecreatefromavif') && function_exists('imageavif');
		}
		return false;
	}

	/**
	 * Add the mimetypes of the optional formats that can be validated on this server to a list of allowed upload types
	 *
	 * @param   array  $mimetypes
	 * @return  array
	 */
	static public function addMimeTypes(array $mimetypes) {
		foreach (self::$optional as $extension => $mimetype) {
			if (self::canValidate($extension) && !in_array($mimetype, $mimetypes)) {
				$mimetypes[] = $mimetype;
			}
		}
		return $mimetypes;
	}

	/**
	 * Add the optional extensions to a list of image extensions that are listed or displayed.
	 * Showing a file that is already there needs no support in PHP, the browser does the work.
	 *
	 * @param   array  $extensions
	 * @return  array
	 */
	static public function addExtensions(array $extensions) {
		return array_values(array_unique(array_merge($extensions, array_keys(self::$optional))));
	}

	/**
	 * Add the getimagesize() type constants of the optional formats this PHP build knows
	 * to a map of imagetype => extension
	 *
	 * @param   array  $map
	 * @return  array
	 */
	static public function addImageTypes(array $map) {
		if (defined('IMAGETYPE_WEBP')) {
			$map[IMAGETYPE_WEBP] = 'webp';
		}
		if (defined('IMAGETYPE_AVIF')) {
			$map[IMAGETYPE_AVIF] = 'avif';
		}
		return $map;
	}

	/**
	 * Get the format to use when saving a resized copy of a file.
	 *
	 * @param   string  $path  Path or file name of the original
	 * @return  string|null  The extension the file can be processed as, or null if GD can't handle it
	 */
	static public function processableExtension($path) {
		$extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
		if ($extension === 'jpeg' || $extension === 'jpe') {
			$extension = 'jpg';
		}
		if (isset(self::$optional[$extension])) {
			return self::canProcess($extension) ? $extension : null;
		}
		return $extension;
	}
}
