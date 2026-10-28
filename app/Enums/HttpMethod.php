<?php

declare(strict_types=1);

namespace App\Enums;

use App\Exceptions\UnsupportedMethodException;

enum HttpMethod: string
{
    case Get = 'GET';
    case Post = 'POST';
    case Put = 'PUT';
    case Patch = 'PATCH';
    case Delete = 'DELETE';
    case Options = 'OPTIONS';
    case Head = 'HEAD';

    public static function fromString(string $method): self
    {
        return self::tryFrom(strtoupper($method))
            ?? throw new UnsupportedMethodException("HTTP method {$method} is not supported");
    }
}
