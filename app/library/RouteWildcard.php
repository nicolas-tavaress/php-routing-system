<?php

namespace app\library;

use app\enums\RouteWildcards as RouteWildcardsEnum;

class RouteWildcard {
    private string $wildcardReplaced;
    private array $params = [];

    public function paramsToArray(string $uri, string $wildcard, array $aliases = []): void{
        $explodeUri = explode('/', ltrim($uri, '/'));
        $explodeWildcard = explode('/', ltrim($wildcard, '/'));
        $diffArrays = array_diff($explodeUri, $explodeWildcard);

        $aliasesIndex = 0;

        foreach ($diffArrays as $index => $value) {
            if (!$aliases) {
                $this->params[array_values($explodeUri)[$index - 1]] = is_numeric($value) ? (int)$value : $value;
            } else {
                $this->params[$aliases[$aliasesIndex]] = is_numeric($value) ? (int)$value : $value;
                $aliasesIndex++;
            }
        }
    }

    public function replaceWildcardWithPattern(string $uri): void {
        $this->wildcardReplaced = $uri;

        if (str_contains($this->wildcardReplaced, '(:numeric)')) {
            $this->wildcardReplaced = str_replace('(:numeric)', RouteWildcardsEnum::numeric->value, $this->wildcardReplaced);
        }

        if (str_contains($this->wildcardReplaced, '(:alpha)')) {
            $this->wildcardReplaced = str_replace('(:alpha)', RouteWildcardsEnum::alpha->value, $this->wildcardReplaced);
        }

        if (str_contains($this->wildcardReplaced, '(:any)')) {
            $this->wildcardReplaced = str_replace('(:any)', RouteWildcardsEnum::any->value, $this->wildcardReplaced);
        }
    }

    public function getWildcardReplaced(): string {
        return $this->wildcardReplaced;
    }

    public function uriEqualToPattern(string $currentUri, string $wildcardReplaced): bool {
        $wildcard = str_replace('/', '\/', ltrim($wildcardReplaced, '/'));

        return preg_match("/^$wildcard$/", ltrim($currentUri, '/'));
    }

    public function getParams(): array {
        return $this->params ? [...$this->params] : [];
    }
}