<?php

class mod_taxonomy_Vocabulary extends icms_ipf_Object {
	public function __construct(&$handler) {
		parent::__construct($handler);

		$this->quickInitVar('vocabulary_id', XOBJ_DTYPE_INT, true);
		$this->quickInitVar('label', XOBJ_DTYPE_TXTBOX, true, 'Label');
		$this->quickInitVar('multiple', XOBJ_DTYPE_INT, false, 'Several terms per object', '', 0);
		// comma-separated object types ("news/article"); empty for all
		$this->quickInitVar('object_types', XOBJ_DTYPE_TXTBOX, false, 'Object types', '', '');
	}
}
