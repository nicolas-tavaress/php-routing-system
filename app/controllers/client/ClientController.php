<?php

namespace app\controllers\client;

class ClientController {
    public function index()
    {
        dd('ClientController@index');
    }

    public function show($clientId)
    {
        dd("ClientController@show: $clientId");
    }
}