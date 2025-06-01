<?php

namespace app\library;

class RouteOptions {
    public function __construct(private readonly array $options) {}

    public function optionExists(string $index): bool {
        return !empty($this->options) && isset($this->options[$index]);
    }

    public function execute(string $index): mixed {
        return $this->options[$index];
    }
}