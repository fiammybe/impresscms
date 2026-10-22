<?php
/**
 * Article administration: an ordinary IPF admin page (table, form, controller)
 */

include_once '../../../include/cp_header.php';

$handler = icms_getModuleHandler('article', 'news');
$op = isset($_POST['op']) ? (string) $_POST['op'] : (isset($_GET['op']) ? (string) $_GET['op'] : '');
$articleId = isset($_GET['article_id']) ? (int) $_GET['article_id'] : (isset($_POST['article_id']) ? (int) $_POST['article_id'] : 0);

switch ($op) {
	case 'mod':
		icms_cp_header();
		$article = $handler->get($articleId);
		echo $article->getForm($article->isNew() ? 'New article' : 'Edit article', 'addarticle')->render();
		break;

	case 'addarticle':
		$controller = new icms_ipf_Controller($handler);
		$controller->storeFromDefaultForm('Article created', 'Article modified');
		break;

	case 'del':
		$controller = new icms_ipf_Controller($handler);
		$controller->handleObjectDeletion();
		break;

	default:
		icms_cp_header();
		$table = new icms_ipf_view_Table($handler);
		$table->addColumn(new icms_ipf_view_Column('title'));
		$table->addIntroButton('addarticle', 'article.php?op=mod', 'Add an article');
		echo $table->fetch();
		break;
}

icms_cp_footer();
