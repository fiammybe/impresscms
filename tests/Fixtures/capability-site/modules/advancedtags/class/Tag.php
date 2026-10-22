<?php

class mod_advancedtags_Tag extends icms_ipf_Object {
	public function __construct(&$handler) {
		parent::__construct($handler);

		$this->quickInitVar('tag_id', XOBJ_DTYPE_INT, true);
		$this->quickInitVar('label', XOBJ_DTYPE_TXTBOX, true);
		$this->quickInitVar('slug', XOBJ_DTYPE_TXTBOX, true);
		$this->quickInitVar('created', XOBJ_DTYPE_INT, false, '', '', 0);
	}
}
