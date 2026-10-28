<?php

declare(strict_types=1);

namespace App\Controllers\Client;

final class ClientController
{
    public function index(): string
    {
        return 'ClientController@index';
    }

    public function show(int $clientId): string
    {
        return "ClientController@show: {$clientId}";
    }
}
