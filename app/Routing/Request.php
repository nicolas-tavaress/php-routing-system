<?php

declare(strict_types=1);

namespace App\Routing;

use App\Enums\HttpMethod;
use Uri\Rfc3986\Uri;

final readonly class Request
{
    public string $path;

    /**
     * @param array<array-key, mixed> $query
     * @param array<string, string> $headers Lower-cased header names.
     */
    public function __construct(
        public HttpMethod $method,
        string $path,
        public array $query = [],
        public array $headers = [],
    ) {
        $this->path = Path::normalize($path);
    }

    /**
     * @param array<string, mixed>|null $server Defaults to $_SERVER.
     */
    public static function fromGlobals(?array $server = null): self
    {
        $server ??= $_SERVER;

        $requestUri = is_string($server['REQUEST_URI'] ?? null) ? $server['REQUEST_URI'] : '/';
        $method = is_string($server['REQUEST_METHOD'] ?? null) ? $server['REQUEST_METHOD'] : 'GET';

        // A leading "//" would be parsed as an authority ("//host/path"), so collapse it first.
        $uri = Uri::parse('/' . ltrim($requestUri, '/'))
            ?? throw new \InvalidArgumentException("Malformed request URI {$requestUri}");

        parse_str($uri->getQuery() ?? '', $query);

        return new self(HttpMethod::fromString($method), $uri->getPath(), $query, self::headersFrom($server));
    }

    public function header(string $name): ?string
    {
        return $this->headers[strtolower($name)] ?? null;
    }

    /**
     * @param array<string, mixed> $server
     * @return array<string, string>
     */
    private static function headersFrom(array $server): array
    {
        $headers = [];
        foreach ($server as $key => $value) {
            if (str_starts_with($key, 'HTTP_') && is_string($value)) {
                $headers[strtolower(str_replace('_', '-', substr($key, 5)))] = $value;
            }
        }

        return $headers;
    }
}
