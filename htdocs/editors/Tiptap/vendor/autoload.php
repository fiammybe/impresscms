<?php

spl_autoload_register(
    function ($class) {
        if (strpos($class, 'Tiptap\\') !== 0) {
            return;
        }

        $path = __DIR__ . '/tiptap-php/src/' . str_replace('\\', '/', substr($class, 7)) . '.php';

        if (is_readable($path)) {
            require_once $path;
        }
    }
);
