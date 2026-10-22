<?php

include_once __DIR__ . '/Category.php';

class mod_news_CategoryHandler extends icms_ipf_Handler {
	public function __construct($db) {
		parent::__construct($db, 'category', 'category_id', 'name', 'name', 'news');
	}
}
