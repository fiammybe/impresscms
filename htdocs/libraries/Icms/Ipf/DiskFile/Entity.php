<?php
/**
 * A file on disk, as an IPF-style object.
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @category	ICMS
 * @package		Ipf
 * @subpackage	DiskFile
 */

declare(strict_types=1);

namespace Icms\Ipf\DiskFile;

defined("ICMS_ROOT_PATH") or die("ImpressCMS root path not defined");

/**
 * The properties of one file inside the root of a {@see Handler}, plus the
 * actions on it: rename, move, copy and delete.
 *
 * Actions never overwrite an existing file and never leave the handler's root;
 * they throw {@see Exception} when refused or when the filesystem fails.
 */
class Entity extends \Icms\Core\Entity
{
	private ?string $mimeType = null;

	public function __construct(private readonly Handler $handler)
	{
		parent::__construct();

		$this->initVar('path', XOBJ_DTYPE_TXTBOX, '', true);
		$this->initVar('dirname', XOBJ_DTYPE_TXTBOX, '');
		$this->initVar('basename', XOBJ_DTYPE_TXTBOX, '');
		$this->initVar('extension', XOBJ_DTYPE_TXTBOX, '');
		$this->initVar('size', XOBJ_DTYPE_INT, 0);
		$this->initVar('mtime', XOBJ_DTYPE_INT, 0);
		$this->initVar('is_readable', XOBJ_DTYPE_INT, 0);
		$this->initVar('is_writable', XOBJ_DTYPE_INT, 0);
	}

	public function handler(): Handler
	{
		return $this->handler;
	}

	/**
	 * Reads the properties of the file at $path from the disk.
	 *
	 * @throws Exception when $path is not a file inside the handler's root
	 */
	public function loadFromPath(string $path): void
	{
		$path = $this->handler->resolveFile($path);
		$info = pathinfo($path);

		$this->assignVar('path', $path);
		$this->assignVar('dirname', $info['dirname'] ?? '');
		$this->assignVar('basename', $info['basename']);
		$this->assignVar('extension', strtolower($info['extension'] ?? ''));
		$this->assignVar('size', (int) filesize($path));
		$this->assignVar('mtime', (int) filemtime($path));
		$this->assignVar('is_readable', is_readable($path) ? 1 : 0);
		$this->assignVar('is_writable', is_writable($path) ? 1 : 0);

		$this->mimeType = null;
		$this->unsetNew();
		$this->unsetDirty();
	}

	/**
	 * The MIME type of the file content, empty when it cannot be determined.
	 * Read on first use: it opens the file.
	 */
	public function mimeType(): string
	{
		if ($this->mimeType === null) {
			$type = function_exists('mime_content_type') ? @mime_content_type($this->path()) : false;
			$this->mimeType = is_string($type) ? $type : '';
		}

		return $this->mimeType;
	}

	public function path(): string
	{
		return (string) $this->getVar('path', 'n');
	}

	/**
	 * @param string $newName a file name, not a path
	 *
	 * @throws Exception
	 */
	public function rename(string $newName): void
	{
		if ($newName === '' || $newName === '.' || $newName === '..' || strpbrk($newName, "/\\\0") !== false) {
			throw new Exception(sprintf('"%s" is not a file name.', $newName));
		}

		$this->relocate(dirname($this->path()) . DIRECTORY_SEPARATOR . $newName);
	}

	/**
	 * @throws Exception
	 */
	public function moveTo(string $targetDir): void
	{
		$this->relocate($this->targetPath($targetDir));
	}

	/**
	 * @return Entity the copy, loaded from its new place
	 *
	 * @throws Exception
	 */
	public function copyTo(string $targetDir): Entity
	{
		$source = $this->handler->resolveFile($this->path());
		$target = $this->targetPath($targetDir);

		if (!@copy($source, $target)) {
			throw new Exception(sprintf('Unable to copy "%s" to "%s".', $source, $target));
		}

		return $this->handler->get($target);
	}

	/**
	 * @throws Exception
	 */
	public function delete(): void
	{
		$path = $this->handler->resolveFile($this->path());

		if (!@unlink($path)) {
			throw new Exception(sprintf('Unable to delete "%s".', $path));
		}

		$this->setNew();
	}

	private function relocate(string $target): void
	{
		$source = $this->handler->resolveFile($this->path());

		if (file_exists($target)) {
			throw new Exception(sprintf('"%s" already exists.', $target));
		}

		if (!@rename($source, $target)) {
			throw new Exception(sprintf('Unable to move "%s" to "%s".', $source, $target));
		}

		$this->loadFromPath($target);
	}

	private function targetPath(string $targetDir): string
	{
		$target = $this->handler->resolveFolder($targetDir) . DIRECTORY_SEPARATOR . $this->getVar('basename', 'n');

		if (file_exists($target)) {
			throw new Exception(sprintf('"%s" already exists.', $target));
		}

		return $target;
	}
}
