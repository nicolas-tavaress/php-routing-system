<?php

namespace app\library;

class Route {
    private ?RouteOptions $routeOptions = null;
    private ?Uri $uri = null;
    private ?RouteWildcard $routeWildcard = null;

    public function __construct(
        public readonly string $request,
        public readonly string $controller,
        public readonly array $wildcardAliases = []
    ) {}

    public function registerGroupOptions(RouteOptions $routeOptions): void {
        $this->routeOptions = $routeOptions;
    }

    public function getRouteOptionsInstance(): ?RouteOptions {
        return $this->routeOptions;
    }

    public function registerRouteUri(Uri $uri): void {
        $this->uri = $uri;
    }

    public function getRouteUriIstance(): ?Uri {
        return $this->uri;
    }

    public function registerRouteWildcard(RouteWildcard $routeWildcard): void {
        $this->routeWildcard = $routeWildcard;
    }

    public function getRouteWildcardInstance(): ?RouteWildcard {
        return $this->routeWildcard;
    }

    public function match(): ?self {
        if ($this->routeOptions->optionExists('prefix')) {
            $this->uri->setUri(rtrim("/{$this->routeOptions->execute('prefix')}{$this->uri->getUri()}", '/'));
        }

        $this->routeWildcard->replaceWildcardWithPattern($this->uri->getUri());
        $wildcardReplaced = $this->routeWildcard->getWildcardReplaced();

        if ($wildcardReplaced !== $this->uri->getUri() && $this->routeWildcard->uriEqualToPattern($this->uri->getRequestUri(), $wildcardReplaced)) {
            $this->uri->setUri($this->uri->getRequestUri());
            $this->routeWildcard->paramsToArray($this->uri->getUri(), $wildcardReplaced, $this->wildcardAliases);
        }

        if ($this->uri->getUri() === $this->uri->getRequestUri() && strtolower($this->request) === $this->uri->getRequestMethod()) {
            return $this;
        }

        return null;
    }
}