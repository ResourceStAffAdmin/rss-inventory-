<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class View
{
    public static function render(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        $viewsBase = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR;
        $viewPath = $viewsBase . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';
        $layoutPath = $viewsBase . str_replace('/', DIRECTORY_SEPARATOR, $layout) . '.php';

        if (!is_file($viewPath) || !is_file($layoutPath)) {
            throw new RuntimeException('View file not found.');
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewPath;
        $content = (string) ob_get_clean();

        require $layoutPath;
    }
}
