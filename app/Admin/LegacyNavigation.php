<?php

declare(strict_types=1);

namespace App\Admin;

use App\Legacy\LegacyRuntime;
use Symfony\Component\HttpFoundation\Response;

/**
 * The legacy admin navigation for migrated pages: the previous page, the page
 * before a form (icms_page_before_form), redirect_header() responses and the
 * security token of legacy forms. Controllers use it instead of icms:: and
 * the legacy functions.
 */
final class LegacyNavigation
{
    public function __construct(
        private readonly LegacyRedirect $redirect,
        private readonly LegacyRuntime $runtime,
    ) {
    }

    /**
     * icms::$urls['previouspage']: the referring page.
     */
    public function previousPage(): string
    {
        $this->runtime->bootTo('module');

        return (string) (\icms::$urls['previouspage'] ?? '');
    }

    /**
     * icms_get_page_before_form(): the page the form was opened from.
     */
    public function pageBeforeForm(): string
    {
        $this->runtime->bootTo('module');

        return (string) icms_get_page_before_form();
    }

    /**
     * ICMS_URL: the site's base URL, without a trailing slash.
     */
    public function siteUrl(): string
    {
        $this->runtime->bootTo('constants');

        return rtrim((string) ICMS_URL, '/');
    }

    public function redirect(string $url, int $seconds, string $message): Response
    {
        return $this->redirect->to($url, $seconds, $message);
    }

    public function back(int $seconds, string $message): Response
    {
        return $this->redirect($this->previousPage(), $seconds, $message);
    }

    /**
     * The hidden input of a fresh ImpressCMS security token.
     */
    public function tokenField(): string
    {
        $this->runtime->bootTo('language');

        return (string) $this->runtime->service('security')->getTokenHTML();
    }

    /**
     * _CO_ICMS_SAVE_ERROR with the errors as Icms\Core\Entity::getHtmlErrors() lists them.
     *
     * @param list<string> $errors
     */
    public static function saveErrorMessage(array $errors): string
    {
        $lines = implode('', array_map(static fn (string $error): string => htmlspecialchars($error, ENT_QUOTES) . '<br />', $errors));

        return _CO_ICMS_SAVE_ERROR . '<h4>' . _ERROR . "</h4>{$lines}";
    }

    /**
     * The message of a refused deletion, with the errors in the format of
     * Icms\Core\Entity::getHtmlErrors().
     *
     * @param list<string> $errors
     */
    public static function deleteErrorMessage(array $errors): string
    {
        $lines = implode('', array_map(static fn (string $error): string => htmlspecialchars($error, ENT_QUOTES) . '<br />', $errors));

        return _CO_ICMS_DELETE_ERROR . '<h4>' . _ERROR . "</h4>{$lines}";
    }
}
