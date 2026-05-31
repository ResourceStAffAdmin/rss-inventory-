<?php

declare(strict_types=1);

$env = static function (string $key): string {
    $value = getenv($key);

    if ($value === false || $value === '') {
        throw new RuntimeException(sprintf('Missing required environment variable: %s', $key));
    }

    return $value;
};

return [
    'name' => 'RSS Inventory',
    'env' => $env('APP_ENV'),
    'debug' => filter_var($env('APP_DEBUG'), FILTER_VALIDATE_BOOL),
    'url' => $env('APP_URL'),
    'timezone' => $env('APP_TIMEZONE'),
];
