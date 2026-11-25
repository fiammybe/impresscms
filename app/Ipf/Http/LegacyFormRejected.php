<?php

declare(strict_types=1);

namespace App\Ipf\Http;

use App\Admin\LegacyNavigation;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * A posted IPF form that failed validation, or that the handler refused to
 * store. Rendered as the legacy save renders it
 * (Icms\Ipf\Controller::storeFromDefaultForm()): back to the previous page
 * after three seconds, with _CO_ICMS_SAVE_ERROR and the errors in the format
 * of Icms\Core\Entity::getHtmlErrors().
 */
final class LegacyFormRejected extends RuntimeException
{
    /**
     * @param list<string> $errors
     */
    public function __construct(public readonly array $errors)
    {
        parent::__construct(implode("\n", $errors));
    }

    public function render(Request $request): Response
    {
        return app(LegacyNavigation::class)->back(3, LegacyNavigation::saveErrorMessage($this->errors));
    }
}
