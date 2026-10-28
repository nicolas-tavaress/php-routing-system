<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\RouteWildcards;
use App\Exceptions\InvalidRouteException;
use App\Routing\RouteCompiler;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RouteCompilerTest extends TestCase
{
    public function testLiteralRouteMatchesOnlyItself(): void
    {
        $route = RouteCompiler::compile('/products');

        self::assertSame([], $route->match('/products'));
        self::assertNull($route->match('/products/1'));
        self::assertNull($route->match('/product'));
    }

    public function testWildcardsUseAliasesAndCastNumbers(): void
    {
        $route = RouteCompiler::compile('/client/(:numeric)/stores/(:numeric)', ['clientId', 'storeId']);

        self::assertSame(['clientId' => 5, 'storeId' => 7], $route->match('/client/5/stores/7'));
        self::assertNull($route->match('/client/abc/stores/7'));
    }

    public function testWildcardWithoutAliasIsNamedAfterPrecedingSegment(): void
    {
        $route = RouteCompiler::compile('/client/(:numeric)/(:any)');

        self::assertSame(['client' => 5, 'param1' => 'x1'], $route->match('/client/5/x1'));
    }

    public function testParamWithSameValueAsLiteralSegment(): void
    {
        $route = RouteCompiler::compile('/stores/(:alpha)', ['name']);

        self::assertSame(['name' => 'stores'], $route->match('/stores/stores'));
    }

    public function testRegexCharactersInLiteralsAreEscaped(): void
    {
        $route = RouteCompiler::compile('/file.json');

        self::assertSame([], $route->match('/file.json'));
        self::assertNull($route->match('/fileXjson'));
    }

    /**
     * @return iterable<string, array{RouteWildcards, string, bool}>
     */
    public static function wildcardProvider(): iterable
    {
        yield 'numeric ok' => [RouteWildcards::Numeric, '123', true];
        yield 'numeric rejects letters' => [RouteWildcards::Numeric, '12a', false];
        yield 'alpha ok' => [RouteWildcards::Alpha, 'abc', true];
        yield 'alpha rejects digits' => [RouteWildcards::Alpha, 'ab1', false];
        yield 'any ok' => [RouteWildcards::Any, 'ab1', true];
        yield 'any rejects dash' => [RouteWildcards::Any, 'a-b', false];
    }

    #[DataProvider('wildcardProvider')]
    public function testWildcardPatterns(RouteWildcards $wildcard, string $value, bool $matches): void
    {
        $route = RouteCompiler::compile("/x/(:{$wildcard->value})", ['value']);

        self::assertSame($matches, $route->match("/x/{$value}") !== null);
    }

    public function testUnknownWildcardThrows(): void
    {
        $this->expectException(InvalidRouteException::class);

        RouteCompiler::compile('/x/(:uuid)');
    }

    public function testMoreAliasesThanWildcardsThrows(): void
    {
        $this->expectException(InvalidRouteException::class);

        RouteCompiler::compile('/x/(:numeric)', ['a', 'b']);
    }

    public function testInvalidAliasThrows(): void
    {
        $this->expectException(InvalidRouteException::class);

        RouteCompiler::compile('/x/(:numeric)', ['not-valid']);
    }
}
