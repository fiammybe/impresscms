<?php

include_once __DIR__ . '/Article.php';

class mod_news_ArticleHandler extends icms_ipf_Handler {
	public function __construct($db) {
		parent::__construct($db, 'article', 'article_id', 'title', 'body', 'news');
	}
}
