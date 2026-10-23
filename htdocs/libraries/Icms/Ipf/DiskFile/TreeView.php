<?php
/**
 * A folder tree as the rows of the IPF tree table template.
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
 * Adapts a {@see TreeSource} (a {@see FolderTree} for the disk) to the template of the IPF table views,
 * `system_persistabletable_display.html`, without the IPF table classes:
 * `Icms\Ipf\View\Table` needs an `Icms\Ipf\Handler` (item name, page, table
 * fields, links to the edit and delete pages), which a folder does not have.
 *
 * It produces the variables that template reads. Assign them to a template
 * object and show the template as the table views do:
 *
 *     $view = new TreeView($tree, $root, ['basename' => 'Name', 'size' => 'Size']);
 *     $tpl->assign($view->templateVariables());
 *     $tpl->display('db:system_persistabletable_display.html');
 *
 * Columns are chosen from `basename`, `extension`, `size` and `mtime`. The
 * first column shows the tree: its text is indented by the node's level and
 * folders are marked. Names and paths come from the disk and are escaped for
 * HTML; a row's id is its escaped path.
 * Actions are links the caller builds for a node, as HTML.
 */
final class TreeView
{
	public const COLUMNS = ['basename', 'extension', 'size', 'mtime'];

	/** @var array<string, string> */
	private array $columns;

	/** @var list<\Closure(TreeNode): string> */
	private array $actions = [];

	/**
	 * @param array<string, string> $columns column key => caption (HTML); the first key is the tree column
	 */
	public function __construct(
		private readonly TreeSource $tree,
		private readonly string $folder,
		array $columns = ['basename' => 'Name', 'size' => 'Size', 'mtime' => 'Modified'],
		private readonly string $id = 'icms_folder_tree',
	) {
		if ($columns === []) {
			throw new \InvalidArgumentException('A tree view needs at least one column.');
		}

		foreach (array_keys($columns) as $key) {
			if (!in_array($key, self::COLUMNS, true)) {
				throw new \InvalidArgumentException(sprintf('Unsupported tree view column "%s".', $key));
			}
		}

		$this->columns = $columns;
	}

	/**
	 * @param \Closure(TreeNode): string $action returns the HTML of one link or button for a node, or '' for none
	 */
	public function addAction(\Closure $action): self
	{
		$this->actions[] = $action;

		return $this;
	}

	/**
	 * @return array<string, mixed> the variables of `system_persistabletable_display.html`
	 *
	 * @throws Exception                 when the folder is not inside the root
	 * @throws \InvalidArgumentException for criteria the handler cannot evaluate
	 */
	public function templateVariables(?\Icms\Db\Criteria\Element $criteria = null, string $sort = 'basename', string $order = 'ASC'): array
	{
		$rows = [];
		$hasActions = false;
		$class = 'odd';

		foreach ($this->tree->nodes($this->folder, $criteria, $sort, $order) as $node) {
			$class = $class === 'odd' ? 'even' : 'odd';

			$actions = array_values(array_filter(array_map(static fn (\Closure $action): string => $action($node), $this->actions), static fn (string $html): bool => $html !== ''));
			$hasActions = $hasActions || $actions !== [];

			$rows[] = [
				// the template prints the id inside an attribute; a path comes from the disk
				'id' => self::escape($node->path),
				'class' => $class,
				'columns' => $this->cells($node),
				'actions' => $actions,
			];
		}

		$columns = [];
		foreach ($this->columns as $key => $caption) {
			$columns[] = ['key' => $key, 'caption' => $caption, 'width' => '', 'align' => $key === 'size' ? 'right' : 'left'];
		}

		return [
			'icms_id' => $this->id,
			'icms_columns' => $columns,
			'icms_persistable_objects' => $rows,
			'icms_colspan' => count($columns) + 1,
			'icms_has_actions' => $hasActions,
			'icms_actions_column_width' => count($this->actions) * 30,
			'icms_show_action_column_title' => false,
			'icms_isTree' => true,
			'icms_showFilterAndLimit' => false,
			'icms_user_side' => false,
		];
	}

	/**
	 * @return list<array{keyname: string, value: string, class: string, width: string, align: string}>
	 */
	private function cells(TreeNode $node): array
	{
		$cells = [];
		$index = 0;

		foreach (array_keys($this->columns) as $key) {
			$cells[] = [
				'keyname' => $key,
				'value' => $index === 0 ? $this->treeCell($node, $key) : $this->valueOf($node, $key),
				'class' => $index === 0 ? 'head' : ($index % 2 === 0 ? 'even' : 'odd'),
				'width' => '',
				'align' => $key === 'size' ? 'right' : 'left',
			];
			$index++;
		}

		return $cells;
	}

	/**
	 * Indented by level, as the IPF tree view does with "--" per level; a folder
	 * is shown in bold with a trailing slash.
	 */
	private function treeCell(TreeNode $node, string $key): string
	{
		$indent = str_repeat('--', $node->level) . ($node->level > 0 ? '&nbsp;' : '');
		$text = $this->valueOf($node, $key);

		return $indent . ($node->isFolder() ? '<strong>' . $text . '/</strong>' : $text);
	}

	private function valueOf(TreeNode $node, string $key): string
	{
		if ($node->file === null) {
			return $key === 'basename' ? self::escape($node->name) : '';
		}

		$value = $node->file->getVar($key, 'n');

		return match ($key) {
			'mtime' => gmdate('Y-m-d H:i', (int) $value),
			'size' => (string) (int) $value,
			default => self::escape((string) $value),
		};
	}

	private static function escape(string $text): string
	{
		return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}
}
