<?php

class mod_news_Article extends icms_ipf_Object {
	public function __construct(&$handler) {
		parent::__construct($handler);

		$this->quickInitVar('article_id', XOBJ_DTYPE_INT, true);
		$this->quickInitVar('title', XOBJ_DTYPE_TXTBOX, true, 'Title');
		$this->quickInitVar('body', XOBJ_DTYPE_TXTAREA, false, 'Body');
		// a variable with the name of a tagging field: the capability fields are namespaced
		$this->quickInitVar('tags', XOBJ_DTYPE_TXTBOX, false, 'Own tags', 'The article\'s own "tags" variable');
	}
}
