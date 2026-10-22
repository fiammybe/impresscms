<?php

class mod_taxonomy_Term extends icms_ipf_Object {
	public function __construct(&$handler) {
		parent::__construct($handler);

		$this->quickInitVar('term_id', XOBJ_DTYPE_INT, true);
		$this->quickInitVar('vocabulary_id', XOBJ_DTYPE_INT, true);
		$this->quickInitVar('parent_id', XOBJ_DTYPE_INT, false, '', '', 0);
		$this->quickInitVar('label', XOBJ_DTYPE_TXTBOX, true);
		$this->quickInitVar('slug', XOBJ_DTYPE_TXTBOX, true);
		$this->quickInitVar('weight', XOBJ_DTYPE_INT, false, '', '', 0);
		// materialized path of zero-padded ids ("/000001/000004/"), for depth-first order and descendants
		$this->quickInitVar('path', XOBJ_DTYPE_TXTBOX, false, '', '', '');
	}
}
