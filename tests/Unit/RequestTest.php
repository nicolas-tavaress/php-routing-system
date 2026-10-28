<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\HttpMethod;
use App\Exceptions\UnsupportedMethodException;
use App\Routing\Request;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    public function testFromGlobalsParsesPathQueryAndHeaders(): void
    {
        $request = Request::fromGlobals([
            'REQUEST_METHOD' => 'post',
            'REQUEST_URI' => '/client/5/?page=2',
            'HTTP_X_ROLE' => 'admin',
        ]);

        self::assertSame(HttpMethod::Post, $request->method);
        self::assertSame('/client/5', $request->path);
        self::assertSame(['page' => '2'], $request->query);
        self::assertSame('admin', $request->header('X-Role'));
    }

    public function testRootWithQueryString(): void
    {
        $request = Request::fromGlobals(['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/?x=1']);

        self::assertSame('/', $request->path);
    }

    public function testRepeatedSlashesAreNotParsedAsAuthority(): void
    {
        $request = Request::fromGlobals(['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '//client//5/']);

        self::assertSame('/client/5', $request->path);
    }

    public function testUnsupportedMethodThrows(): void
    {
        $this->expectException(UnsupportedMethodException::class);

        Request::fromGlobals(['REQUEST_METHOD' => 'BREW', 'REQUEST_URI' => '/']);
    }
}
