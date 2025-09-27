<?php

declare(strict_types=1);

/**
 * Base class with sensible defaults.
 */
abstract class icms_form_content_AbstractContentTypeHandler implements icms_form_content_ContentTypeHandlerInterface
{
    public function processForStorage(string $input): string
    {
        return $input; // passthrough by default
    }
}

