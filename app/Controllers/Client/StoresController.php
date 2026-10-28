<?php

declare(strict_types=1);

namespace App\Controllers\Client;

final class StoresController
{
    /**
     * @return array{clientId: int, storeId: int}
     */
    public function list(int $clientId, int $storeId): array
    {
        return ['clientId' => $clientId, 'storeId' => $storeId];
    }

    /**
     * @return array{clientId: int}
     */
    public function show(int $clientId): array
    {
        return ['clientId' => $clientId];
    }
}
