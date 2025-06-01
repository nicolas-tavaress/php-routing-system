<?php

namespace app\middlewares;

use app\interfaces\MiddlewareInterface;

class Admin implements MiddlewareInterface {
    public function execute() {
        dd('Admin middleware executed');
    }
}