<?php

declare(strict_types=1);

namespace App\Controllers;

final class NotFoundController
{
    public function index(): string
    {
        http_response_code(404);

        return '404 Not Found';
    }
}
