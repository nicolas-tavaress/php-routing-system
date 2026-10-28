<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\HttpMethod;
use App\Exceptions\MiddlewareNotFoundException;
use App\Middlewares\Auth;
use App\Routing\MiddlewarePipeline;
use App\Routing\Request;
use PHPUnit\Framework\TestCase;
use Tests\Fixtures\Middlewares\BlockMiddleware;
use Tests\Fixtures\Middlewares\TraceMiddleware;

final class MiddlewarePipelineTest extends TestCase
{
    private Request $request;

    protected function setUp(): void
    {
        $this->request = new Request(HttpMethod::Get, '/');
    }

    public function testRunsMiddlewaresInOrderAroundDestination(): void
    {
        $pipeline = new MiddlewarePipeline([new TraceMiddleware('a'), new TraceMiddleware('b')]);

        self::assertSame('a(b(action))', $pipeline->process($this->request, static fn (): string => 'action'));
    }

    public function testMiddlewareCanStopThePipeline(): void
    {
        $called = false;
        $pipeline = new MiddlewarePipeline([new BlockMiddleware()]);

        $result = $pipeline->process($this->request, static function () use (&$called): string {
            $called = true;

            return 'action';
        });

        self::assertSame('blocked', $result);
        self::assertFalse($called);
    }

    public function testResolvesEnumAliasesAndClassNames(): void
    {
        $pipeline = MiddlewarePipeline::fromAliases(['auth', BlockMiddleware::class]);

        // Auth rejects requests without an Authorization header.
        self::assertSame('Unauthorized', $pipeline->process($this->request, static fn (): string => 'action'));

        $authorized = new Request(HttpMethod::Get, '/', headers: ['authorization' => 'Bearer x']);
        self::assertSame('blocked', $pipeline->process($authorized, static fn (): string => 'action'));
        self::assertTrue(class_exists(Auth::class));
    }

    public function testUnknownMiddlewareThrows(): void
    {
        $this->expectException(MiddlewareNotFoundException::class);

        MiddlewarePipeline::fromAliases(['e2e']);
    }
}
