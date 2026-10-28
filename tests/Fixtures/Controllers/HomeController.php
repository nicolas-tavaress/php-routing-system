<?php

declare(strict_types=1);

namespace Tests\Fixtures\Controllers;

final class HomeController
{
    public function index(): string
    {
        return 'home';
    }

    /**
     * @return array<int|string, int|string>
     */
    public function params(int|string ...$params): array
    {
        return $params;
    }
}
