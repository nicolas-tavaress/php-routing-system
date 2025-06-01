<?php

namespace app\library;

class Uri {
    public function __construct(private string $uri) {}

    public function setUri(string $uri):void {
        $this->uri = $uri;
    }

    public function getUri(): string {
        return $this->uri;
    }

    public function getRequestUri(): string {
        return $_SERVER['REQUEST_URI'] !== '/' ? rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') : '/';
    }

    public function getRequestMethod(): string {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }
}