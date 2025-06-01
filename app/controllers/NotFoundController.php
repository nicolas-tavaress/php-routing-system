<?php

namespace app\controllers;

class NotFoundController {
    public function index() {
        http_response_code(404);
        echo "404 Not Found";
    }
}