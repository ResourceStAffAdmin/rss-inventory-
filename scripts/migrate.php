<?php

declare(strict_types=1);

use App\Core\Database;
use App\Core\Env;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Env.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Database.php';

Env::load(dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env');

$pdo = Database::connection();

$runSqlFolder = static function (string $folder) use ($pdo): void {
    $files = glob($folder . DIRECTORY_SEPARATOR . '*.sql') ?: [];
    sort($files);

    foreach ($files as $file) {
        $sql = file_get_contents($file);
        if ($sql === false || trim($sql) === '') {
            continue;
        }

        $pdo->exec($sql);
        echo 'Applied: ' . basename($file) . PHP_EOL;
    }
};

$base = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'database';
$runSqlFolder($base . DIRECTORY_SEPARATOR . 'migrations');
$runSqlFolder($base . DIRECTORY_SEPARATOR . 'seeders');

echo 'Database setup complete.' . PHP_EOL;
