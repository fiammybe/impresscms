<?php

class mod_taxonomy_Relation extends icms_ipf_Object {
	public function __construct(&$handler) {
		parent::__construct($handler);

		$this->quickInitVar('relation_id', XOBJ_DTYPE_INT, true);
		$this->quickInitVar('term_id', XOBJ_DTYPE_INT, true);
		$this->quickInitVar('module', XOBJ_DTYPE_TXTBOX, true);
		$this->quickInitVar('item', XOBJ_DTYPE_TXTBOX, true);
		$this->quickInitVar('object_id', XOBJ_DTYPE_INT, true);
	}
}
