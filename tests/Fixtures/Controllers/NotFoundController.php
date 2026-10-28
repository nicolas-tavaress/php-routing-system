<?php

declare(strict_types=1);

namespace Tests\Fixtures\Controllers;

final class NotFoundController
{
    public function index(): string
    {
        return 'not found';
    }
}
