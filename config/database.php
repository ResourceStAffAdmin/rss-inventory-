<?php

declare(strict_types=1);

$env = static function (string $key, bool $allowEmpty = false): string {
    $value = getenv($key);

    if ($value === false || (!$allowEmpty && $value === '')) {
        throw new RuntimeException(sprintf('Missing required environment variable: %s', $key));
    }

    return $value;
};

return [
    'host' => $env('DB_HOST'),
    'port' => (int) $env('DB_PORT'),
    'name' => $env('DB_NAME'),
    'user' => $env('DB_USER'),
    'pass' => $env('DB_PASS', true),
    'charset' => $env('DB_CHARSET'),
];
