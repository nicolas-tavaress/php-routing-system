<?php

namespace app\controllers\client;

class StoresController
{
    public function list($clientId, $storeId)
    {
        dd([$clientId, $storeId]);
    }

    public function show($clientId)
    {
        dd($clientId);
    }
}
