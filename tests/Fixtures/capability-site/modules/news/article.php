<?php
/**
 * One article on the user side: the generic IPF single view and the module template
 */

include_once '../../mainfile.php';

$xoopsOption['template_main'] = 'news_article.html';
include_once ICMS_ROOT_PATH . '/header.php';

$article = icms_getModuleHandler('article', 'news')->get(isset($_GET['article_id']) ? (int) $_GET['article_id'] : 0);
if ($article->isNew()) {
	redirect_header(ICMS_URL . '/', 2, 'No such article');
}

$singleView = new icms_ipf_view_Single($article);
$singleView->addRow(new icms_ipf_view_Row('title'));
$singleView->addRow(new icms_ipf_view_Row('body'));

$icmsTpl->assign('news_article', $article->toArray());
$icmsTpl->assign('news_article_object', $article);
$icmsTpl->assign('news_singleview', $singleView->render(true));

include_once ICMS_ROOT_PATH . '/footer.php';
