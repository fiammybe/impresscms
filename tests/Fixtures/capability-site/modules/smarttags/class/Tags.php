<?php

class mod_smarttags_Tags extends icms_ipf_Object {
	public function __construct(&$handler) {
		parent::__construct($handler);

		$this->quickInitVar('tag_id', XOBJ_DTYPE_INT, true);
		$this->quickInitVar('label', XOBJ_DTYPE_TXTBOX, true);
		$this->quickInitVar('slug', XOBJ_DTYPE_TXTBOX, true);
	}
}
