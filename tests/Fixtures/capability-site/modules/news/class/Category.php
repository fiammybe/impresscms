<?php

class mod_news_Category extends icms_ipf_Object {
	public function __construct(&$handler) {
		parent::__construct($handler);

		$this->quickInitVar('category_id', XOBJ_DTYPE_INT, true);
		$this->quickInitVar('name', XOBJ_DTYPE_TXTBOX, true, 'Name');
	}
}
