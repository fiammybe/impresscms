<?php

declare(strict_types=1);

namespace App\Session\Storage;

use App\Session\Database\Handler;
use App\Session\Settings;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Storage\MetadataBag;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageFactoryInterface;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

/**
 * The host's session storage (design D1, D2 of symfony-owns-the-session):
 * native PHP sessions in the ImpressCMS `session` table, with the session
 * name and lifetime of the ImpressCMS preferences.
 */
final class Factory implements SessionStorageFactoryInterface
{
    public function __construct(
        private readonly Settings $settings,
        private readonly Handler $handler,
    ) {
    }

    public function createStorage(?Request $request): SessionStorageInterface
    {
        $options = [
            'name' => $this->settings->name(),
            'cookie_lifetime' => $this->settings->lifetime(),
            'cookie_httponly' => true,
            'cookie_samesite' => 'lax',
            'cookie_secure' => $request?->isSecure() ?? false,
            'cookie_path' => ($request?->getBasePath() ?: '') . '/',
            'use_strict_mode' => true,
            // no cache headers from PHP or the session handler: Symfony's session
            // listener sets them on its responses, App\Session\Host on legacy pages
            'cache_limiter' => '0',
        ];
        if ($this->settings->lifetime() > 0) {
            $options['gc_maxlifetime'] = $this->settings->lifetime();
        }

        return new NativeSessionStorage($options, $this->handler, new MetadataBag());
    }
}
