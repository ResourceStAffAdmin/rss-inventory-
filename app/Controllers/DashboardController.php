<?php

declare(strict_types=1);

namespace App\Controllers;

final class DashboardController
{
    public function index(): void
    {
        (new UiController())->dashboard();
    }
}
