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
 * Create a field for setting group permissions
 *
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 *
 * @category	ICMS
 * @package		Form
 * @subpackage	Elements
 */
namespace Icms\Form\Elements;
defined('ICMS_ROOT_PATH') or die('ImpressCMS root path not defined');

/**
 * Renders checkbox options for a group permission form
 *
 * @category	ICMS
 * @package		Form
 * @subpackage	Elements
 *
 * @author		Kazumi Ono <onokazu@myweb.ne.jp>
 * @copyright	copyright (c) 2000-2003 XOOPS.org
 */
class Groupperm extends \Icms\Form\Element {
	/**
	 * Pre-selected value(s)
	 *
	 * @var array;
	 */
	var $_value = array ();
	/**
	 * Group ID
	 *
	 * @var int
	 */
	var $_groupId;
	/**
	 * Option tree
	 *
	 * @var array
	 */
	var $_optionTree;

	/**
	 * Constructor
	 */
	public function __construct($caption, $name, $groupId, $values = null) {
		$this->setCaption($caption);
		$this->setName($name);
		if (isset ($values)) {
			$this->setValue($values);
		}
		$this->_groupId = $groupId;
	}

	/**
	 * Sets pre-selected values
	 *
	 * @param mixed $value A group ID or an array of group IDs
	 * @access public
	 */
	public function setValue($value): void {
		if (is_array($value)) {
			foreach ($value as $v) {
				$this->setValue($v);
			}
		} else {
			$this->_value[] = $value;
		}
	}

	/**
	 * Sets the tree structure of items
	 *
	 * @param array $optionTree
	 * @access public
	 */
	function setOptionTree($optionTree) {
		$this->_optionTree = $optionTree;
	}

	/**
	 * Renders checkbox options for this group
	 *
	 * @return string
	 * @access public
	 */
	public function render(): string
	{
		return $this->renderDefinition();
	}

	/**
	 * The definition: a choice field of type "groupperm"; the options are the
	 * items of the tree (value: item id), attributes hold the group id and the
	 * item tree
	 */
	protected function describe(): \Icms\Form\Definition\Field
	{
		$options = [];
		foreach ((array) $this->_optionTree as $id => $item) {
			if (!empty($id)) {
				$options[$id] = $item['name'] ?? '';
			}
		}
		return new \Icms\Form\Definition\ChoiceField(...$this->definitionBase('groupperm') + [
			'options' => \Icms\Form\Definition\Choice::fromArray($options),
			'selected' => array_map('strval', $this->_value),
			'multiple' => true,
			'attributes' => ['group_id' => $this->_groupId, 'tree' => (array) $this->_optionTree],
		]);
	}

	protected function definitionTemplate(): ?string
	{
		return 'icms_form_elements_groupperm_display.html';
	}

	/**
	 * The tree as the template shows it: rows of at most 4 columns, one per
	 * top item, each with its items in tree order (prefix, input names, the
	 * inputs of its parents and children), and the names of all inputs for
	 * the "all" checkbox
	 */
	protected function viewData(): array
	{
		$name = $this->getName();
		$columns = [];
		foreach ($this->_optionTree[0]['children'] ?? [] as $top) {
			$nodes = [];
			$this->collectOptionTree($nodes, $this->_optionTree[$top], '');
			$columns[] = $nodes;
		}
		$inputs = [];
		foreach (array_keys((array) $this->_optionTree) as $id) {
			if (!empty($id)) {
				$inputs[] = $name . '[groups][' . $this->_groupId . '][' . $id . ']';
			}
		}
		return [
			'rows' => array_chunk($columns, 4),
			'inputs' => $inputs,
			'checkall' => $name . '[checkallbtn][' . $this->_groupId . ']',
			'all_label' => _ALL,
		];
	}

	/**
	 * Collect the items of a tree, depth first
	 *
	 * @param array $nodes
	 * @param array $option
	 * @param string $prefix
	 * @param array $parentIds
	 */
	private function collectOptionTree(array &$nodes, array $option, string $prefix, array $parentIds = []): void
	{
		$name = $this->getName();
		$input = $name . '[groups][' . $this->_groupId . '][';
		$nodes[] = [
			'id' => $option['id'],
			'label' => $option['name'],
			'prefix' => $prefix,
			'input' => $input . $option['id'] . ']',
			'parents' => array_map(static fn ($id): string => $input . $id . ']', $parentIds),
			'children' => array_map(static fn ($id): string => $input . $id . ']', $option['allchild'] ?? []),
			'checked' => in_array($option['id'], $this->_value),
			'parents_input' => $name . '[parents][' . $option['id'] . ']',
			'parents_value' => implode(':', $parentIds),
			'itemname_input' => $name . '[itemname][' . $option['id'] . ']',
		];
		foreach ($option['children'] ?? [] as $child) {
			$parentIds[] = $option['id'];
			$this->collectOptionTree($nodes, $this->_optionTree[$child], $prefix . '&nbsp;-', $parentIds);
		}
	}
}
