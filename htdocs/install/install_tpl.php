<?php
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/
/**
 * Installer tables creation page
 *
 * See the enclosed file license.txt for licensing information.
 * If you did not receive this file, get it at http://www.fsf.org/copyleft/gpl.html
 *
 * @copyright   The XOOPS project http://www.xoops.org/
 * @license      http://www.fsf.org/copyleft/gpl.html GNU General Public License (GPL)
 * @package        installer
 * @since       2.3.0
 * @author        Haruki Setoyama  <haruki@planewave.org>
 * @author        Kazumi Ono <webmaster@myweb.ne.jp>
 * @author        Skalpa Keo <skalpa@xoops.org>
 * @author        Taiwen Jiang <phppp@users.sourceforge.net>
 * @version        $Id: install_tpl.php 12329 2013-09-19 13:53:36Z skenow $
 */
defined('XOOPS_INSTALL') or die();
if (isset($_COOKIE['xo_install_lang'])) {
	$icmsConfig['language'] = $icmsConfig['language'] = htmlentities($_COOKIE['xo_install_lang']);
}

?><!doctype html>
<html lang="en">
<head>
	<title><?php echo sprintf(XOOPS_INSTALL_WIZARD, XOOPS_VERSION); ?>
		(<?php echo ($wizard->currentPage + 1) . '/' . count($wizard->pages); ?>)</title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo _INSTALL_CHARSET ?>"/>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
	<script src="../libraries/jquery/jquery.js"></script>
</head>
<body>
	<header>
		<div class="d-flex flex-column flex-md-row align-items-center pb-3 mb-4 border-bottom">
			<a href="/" class="d-flex align-items-center link-body-emphasis text-decoration-none">
				<img src="./images/logo.svg" height="72" alt="ImpressCMS logo">
				<span class="fs-4">New Wave Installer</span>
			</a>

			<nav class="d-inline-flex mt-2 mt-md-0 ms-md-auto">
				<a class="me-3 py-2 link-body-emphasis text-decoration-none" href="#"><?php echo sprintf(XOOPS_INSTALL_WIZARD, XOOPS_VERSION); ?></a>
			</nav>
		</div>
	</header>

	<main>
		<form action='<?php echo htmlentities($_SERVER['PHP_SELF']); ?>' method='post'>
		<div class="row">
			<div class="col-md-3 offset-lg-1 d-none d-md-block">
				<h3><?php echo INSTALL_H3_STEPS; ?></h3>
				<ul class="nav nav-pills flex-column mb-auto">
					<?php foreach ($wizard->pages as $k => $page) {
						$class = '';
						if ($k == $wizard->currentPage)
						{
							$class = 'active';
							$icon = 'bi-arrow-right-circle';
							$li = '<a class="nav-link active" href="' . $wizard->pageURI($page) . '"><i class=' .$icon .'></i><strong> ' . $wizard->pagesNames[$k] . '</strong></a>';
						}
						elseif ($k < $wizard->currentPage)
						{
							$class = '';
							$icon = 'bi-check2-circle';
							$li = '<a class="nav-link" href="' . $wizard->pageURI($page) . '"><i class=' .$icon .'></i><strong> ' . $wizard->pagesNames[$k] . '</strong></a>';
						}
						else {
							$class = '';
							$icon = 'bi-circle';
							$li = '<a class="nav-link" href=""><i class=' .$icon .'></i> ' . $wizard->pagesNames[$k] . '</a>';
						}
//						if (empty($class)) {
//							$li = '<a class="nav-link" href="' . $wizard->pageURI($page) . '">' . $wizard->pagesNames[$k] . '</a>';
//							$icon = '"bi-circle"';
//						} else {
//							$li = '<a class="nav-link" href="' . $wizard->pageURI($page) . '">' . $wizard->pagesNames[$k] . '</a>';
//							$icon = '"bi-circle"';
//						}
						echo "<li class='nav-item'>$li</li>\n";
					} ?>
				</ul>
			</div>

				<div class="col-md-7 col-12" id="<?php echo $wizard->currentPageName; ?>">
					<h1 class="text-center mb-2"><?php echo $wizard->pagesTitles[$wizard->currentPage]; ?></h1>
					<?php echo $content; ?>
					<div class="d-flex gap-2 justify-content-center py-5">
					<?php if ($wizard->currentPage != 0 && ($wizard->currentPage != 11)) { ?>
						<a class="btn btn-outline-secondary d-inline-flex align-items-center" href="<?php echo $wizard->pageURI('-1'); ?>" type="button">
							<i class="bi-arrow-left-short"></i> <?php echo BUTTON_PREVIOUS; ?>
						</a>
					<?php } ?>
					<?php if ($wizard->currentPage == 11) { ?>
						<a class="btn btn-primary d-inline-flex align-items-center" href="<?php echo $wizard->pageURI('11'); ?>?success=true" type="button">
							<?php echo BUTTON_SHOW_SITE; ?> <i class="bi-house"></i>
						</a>
					<?php } ?>
					<?php if ($wizard->pages[$wizard->currentPage] == $wizard->secondlastpage) { ?>
					<?php if (@$pageHasForm) { ?>
					<button class="btn btn-primary d-inline-flex align-items-center" type="submit">
						<?php } else { ?>
						<button class="btn btn-primary d-inline-flex align-items-center" type="button" title="<?php echo BUTTON_NEXT; ?>" accesskey="n"
								onclick="location.href='<?php echo $wizard->pageURI('+1'); ?>'" class="next">
							<?php } ?>
							<?php if ($_POST['mod'] != 1) { ?>
								<?php echo BUTTON_NEXT; ?> <i class="bi-arrow-right-short"></i>
							<?php } else { ?>
								<?php echo BUTTON_FINISH; ?>
							<?php } ?>
						</button>
						<?php } else if ($wizard->pages[$wizard->currentPage] != $wizard->lastpage) { ?>
						<?php if (@$pageHasForm) { ?>
						<button class="btn btn-primary d-inline-flex align-items-center" type="submit" title="<?php echo BUTTON_NEXT; ?>">
							<?php } else { ?>
							<button class="btn btn-primary d-inline-flex align-items-center" type="button" title="<?php echo BUTTON_NEXT; ?>" accesskey="n"
									onclick="location.href='<?php echo $wizard->pageURI('+1'); ?>'" class="next">
								<?php } ?>
								<?php echo BUTTON_NEXT; ?> <i class="bi-arrow-right-short"></i>
							</button>
							<?php } ?>
				</div>


		</div>
		</form>
	</main>
	<div id="footer">
		<?php echo INSTALL_COPYRIGHT; ?>
	</div>
</body>
</html>