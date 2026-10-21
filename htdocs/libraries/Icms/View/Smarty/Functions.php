<?php

namespace Icms\View\Smarty;

use Smarty\Template;
use WideImage\WideImage;

/**
 * Template tags specific to ImpressCMS, registered as Smarty function plugins
 * by Icms\View\Tpl.
 */
class Functions
{
    /**
     * Names of the tags this class provides, mapped to its static methods.
     *
     * @return array<string, string>
     */
    public static function tags(): array
    {
        return [
            'xoAppUrl' => 'appUrl',
            'xoImgUrl' => 'imageUrl',
            'xoInboxCount' => 'inboxCount',
            'xoPageNav' => 'pageNav',
            'xoops_link' => 'link',
            'resized_image' => 'resizedImage',
        ];
    }

    /**
     * <{xoAppUrl url="modules/x/y.php" key=value}>, <{xoAppUrl url="."}> for the current URL.
     *
     * @param array<string, mixed> $params
     */
    public static function appUrl(array $params, Template $template): string
    {
        $url = (string) ($params['url'] ?? '/');
        unset($params['url']);

        if ($url === '.') {
            return htmlspecialchars((string) $_SERVER['REQUEST_URI']);
        }

        if (str_starts_with($url, '/')) {
            $url = "www$url";
        }

        if ($params !== []) {
            $url = \icms::buildUrl($url, $params);
        }

        return htmlspecialchars(\icms::path($url, true));
    }

    /**
     * <{xoImgUrl path="icons/favicon.ico"}>: URL of a theme resource, with theme inheritance.
     *
     * @param array<string, mixed> $params
     */
    public static function imageUrl(array $params, Template $template): string
    {
        global $xoTheme;

        $path = trim((string) ($params['path'] ?? ''));

        if (isset($xoTheme) && is_object($xoTheme)) {
            $path = $xoTheme->resourcePath($path);
        }

        return \icms::url($path);
    }

    /**
     * <{xoInboxCount assign=count}>: unread private messages of the current user.
     *
     * @param array<string, mixed> $params
     */
    public static function inboxCount(array $params, Template $template): string
    {
        if (! isset(\icms::$user) || ! is_object(\icms::$user)) {
            return '';
        }

        $time = time();

        if (isset($_SESSION['xoops_inbox_count']) && ($_SESSION['xoops_inbox_count_expire'] ?? 0) > $time) {
            $count = (int) $_SESSION['xoops_inbox_count'];
        } else {
            $handler = \icms::handler('icms_data_privmessage');
            $criteria = new \icms_db_criteria_Compo(new \icms_db_criteria_Item('read_msg', 0));
            $criteria->add(new \icms_db_criteria_Item('to_userid', \icms::$user->getVar('uid')));
            $count = (int) $handler->getCount($criteria);
            $_SESSION['xoops_inbox_count'] = $count;
            $_SESSION['xoops_inbox_count_expire'] = $time + 60;
        }

        if (! empty($params['assign'])) {
            $template->assign($params['assign'], $count);

            return '';
        }

        return (string) $count;
    }

    /**
     * <{xoPageNav itemsCount=$n pageSize=$size offset=$offset linksCount=5 url="list.php?offset=%s"}>
     *
     * @param array<string, mixed> $params
     */
    public static function pageNav(array $params, Template $template): string
    {
        $itemsCount = (int) ($params['itemsCount'] ?? 0);
        $pageSize = (int) ($params['pageSize'] ?? 10);
        $offset = (int) ($params['offset'] ?? 0);
        $linksCount = (int) ($params['linksCount'] ?? 5);
        $url = (string) ($params['url'] ?? '');

        if ($pageSize < 1) {
            $pageSize = 10;
        }

        $pagesCount = (int) ($itemsCount / $pageSize);

        if ($itemsCount <= $pageSize || $pagesCount < 2) {
            return '';
        }

        $links = '';
        $currentPage = (int) ($offset / $pageSize) + 1;
        $lastPage = (int) ($itemsCount / $pageSize) + 1;
        $minPage = min(1, ceil($currentPage - $linksCount / 2));
        $maxPage = max($lastPage, floor($currentPage + $linksCount / 2));

        if ($currentPage > 1) {
            $links .= '<a href="' . \icms::url(str_replace('%s', $offset - $pageSize, $url)) . '">Previous</a>';
        }

        for ($i = $minPage; $i <= $maxPage; $i++) {
            $target = htmlspecialchars(\icms::url(str_replace('%s', ($i - 1) * $pageSize, $url)), ENT_QUOTES);
            $links .= "<a href='$target'>$i</a>";
        }

        if ($currentPage < $lastPage) {
            $links .= '<a href="' . \icms::url(str_replace('%s', $offset + $pageSize, $url)) . '">Next</a>';
        }

        $class = empty($params['class']) ? 'pagenav' : htmlspecialchars((string) $params['class'], ENT_QUOTES);

        return "<div class='$class'>$links</div>";
    }

    /**
     * <{xoops_link module="news" page="index.php" urlvars="storyid"}>
     *
     * @param array<string, mixed> $params
     */
    public static function link(array $params, Template $template): string
    {
        $query = '';

        if (isset($params['urlvars'])) {
            $pairs = [];

            foreach (explode('&', (string) $params['urlvars']) as $variable) {
                $position = strpos($variable, '=');

                if ($position !== false && $position !== 0) {
                    $pairs[] = $variable;

                    continue;
                }

                if (isset($_POST[$variable])) {
                    $pairs[] = "$variable={$_POST[$variable]}";

                    continue;
                }

                if (isset($_GET[$variable])) {
                    $pairs[] = "$variable={$_GET[$variable]}";
                }
            }

            $query = $pairs === [] ? '' : '?' . implode('&', $pairs);
        }

        $module = $params['module'] ?? '';

        if (! isset($params['module']) && isset($GLOBALS['xoopsModule']) && is_object($GLOBALS['xoopsModule'])) {
            $module = $GLOBALS['xoopsModule']->getVar('dirname');
        }

        $page = $params['page'] ?? substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], '/') + 1);

        return empty($module) ?
            ICMS_URL . "/$page" . $query :
            ICMS_URL . "/modules/$module/$page" . $query;
    }

    /**
     * <{resized_image file="/uploads/a.jpg" width=100 height=80 fit="inside" return="img|url"}>
     *
     * @param array<string, mixed> $params
     */
    public static function resizedImage(array $params, Template $template): string
    {
        $options = [
            'file' => '',
            'height' => '',
            'width' => '',
            'fit' => 'inside',
            'return' => 'img',
            'path_prefix' => '',
            'basedir' => $_SERVER['DOCUMENT_ROOT'] ?? '',
            'alt' => '',
        ];
        $extra = '';
        $prefix = '';
        $suffix = '';

        foreach ($params as $key => $value) {
            if ($key === 'link' || $key === 'href') {
                $prefix = '<a href="' . $value . '">';
                $suffix = '</a>';

                continue;
            }

            if (is_array($value)) {
                trigger_error("resized_image: extra attribute '$key' cannot be an array", E_USER_NOTICE);

                continue;
            }

            if ($key === 'alt') {
                $options['alt'] = htmlspecialchars((string) $value, ENT_QUOTES);

                continue;
            }

            if (array_key_exists($key, $options)) {
                $options[$key] = $value;

                continue;
            }

            $extra .= ' ' . $key . '="' . htmlspecialchars((string) $value, ENT_QUOTES) . '"';
        }

        $file = $options['file'];
        $width = $options['width'];
        $height = $options['height'];
        $basedir = $options['basedir'];

        if (empty($file)) {
            trigger_error("resized_image: missing 'file' parameter", E_USER_ERROR);

            return '';
        }

        if (! isset($params['width']) && ! isset($params['height'])) {
            trigger_error('resized_image: New size was not specified', E_USER_ERROR);

            return '';
        }

        if ($options['fit'] === 'fill' && (empty($width) || empty($height))) {
            trigger_error("resized_image:  When you choose 'fill' fit, you have to specify both width and height", E_USER_ERROR);
        }

        $subPath = str_replace($basedir, '', ICMS_ROOT_PATH);
        $clean = str_replace(' ', '_', $file);
        $clean = str_replace(
            explode(' ', 'Á á É é è ê È Ê Í í Ó ó Ú ú Ñ ñ Ç ç'),
            explode(' ', 'A a E e e e E E I i O o U u N n C c'),
            strtolower($clean)
        );
        $clean = str_replace('%2F', '/', urlencode($clean));
        $clean = str_replace($subPath, '', $clean);

        if (str_starts_with($file, '/')) {
            $originalPath = $basedir . $file;
            $clean = str_replace($basedir, '', urldecode($clean));
            $resizedPath = ICMS_ROOT_PATH . '/cache' . $clean;
            $resizedUrl = ICMS_URL . '/cache' . $clean;
        } elseif (str_starts_with($file, ICMS_URL)) {
            $originalPath = ICMS_ROOT_PATH . str_replace(ICMS_URL, '', $file);
            $clean = str_replace(ICMS_URL, '', urldecode($clean));
            $resizedPath = ICMS_ROOT_PATH . '/cache' . $clean;
            $resizedUrl = ICMS_URL . '/cache' . $clean;
        } else {
            $originalPath = $file;
            $clean = str_replace(ICMS_ROOT_PATH, '', urldecode($clean));
            $resizedPath = ICMS_ROOT_PATH . '/cache/' . $clean;
            $resizedUrl = ICMS_URL . '/cache/' . $clean;
        }

        if (! @getimagesize($originalPath)) {
            $reason = match (true) {
                ! file_exists($originalPath) => "unable to find '$originalPath'",
                ! is_readable($originalPath) => "unable to read '$originalPath'",
                default => "'$originalPath' is not a valid image file",
            };
            trigger_error("resized_image: $reason", E_USER_NOTICE);

            return '';
        }

        $policy = $template->getSmarty()->security_policy;

        if ($policy !== null) {
            try {
                $policy->isTrustedResourceDir($originalPath, false);
            } catch (\Smarty\Exception) {
                trigger_error("resized_image: (secure) '$originalPath' not in secure directory", E_USER_NOTICE);
            }
        }

        $suffixName = "-{$width}x{$height}";
        $resizedDir = substr($resizedPath, 0, strrpos($resizedPath, '/'));
        $dot = strrpos($resizedPath, '.');
        $resizedPath = substr($resizedPath, 0, $dot) . $suffixName . substr($resizedPath, $dot);
        $dot = strrpos($resizedUrl, '.');
        $resizedUrl = substr($resizedUrl, 0, $dot) . $suffixName . substr($resizedUrl, $dot);

        if (! file_exists($resizedPath) || filemtime($originalPath) > filemtime($resizedPath)) {
            if (! is_dir($resizedDir)) {
                mkdir($resizedDir, 0755, true);
            }

            WideImage::load($originalPath, 'jpg')
                ->resize($width, $height, $options['fit'])
                ->saveToFile($resizedPath);
        }

        if ($options['return'] === 'url') {
            return $resizedUrl;
        }

        return $prefix . '<img src="' . $resizedUrl . '" alt="' . $options['alt'] . '" ' . $extra . ' />' . $suffix;
    }
}
