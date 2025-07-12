<?php

declare(strict_types=1);

// Define Laravel helper functions for PHPStan analysis
if (!function_exists('config')) {
    function config($key = null, $default = null) {
        return $default;
    }
}

if (!function_exists('app_path')) {
    function app_path($path = '') {
        return __DIR__ . '/app' . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }
}

if (!function_exists('config_path')) {
    function config_path($path = '') {
        return __DIR__ . '/config' . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }
}