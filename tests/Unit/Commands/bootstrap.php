<?php

// Mock functions for testing RepositoryMakeCommand

namespace ElegantMedia\SimpleRepository\Commands;

if (!function_exists('ElegantMedia\SimpleRepository\Commands\config')) {
    function config($key, $default = null) {
        return $default;
    }
}

if (!function_exists('ElegantMedia\SimpleRepository\Commands\app_path')) {
    function app_path($path = '') {
        return '/app' . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }
}

namespace ElegantMedia\SimpleRepository\Tests\Unit\Commands;

if (!function_exists('ElegantMedia\SimpleRepository\Tests\Unit\Commands\app_path')) {
    function app_path($path = '') {
        return '/app' . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }
}