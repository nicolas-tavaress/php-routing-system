<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\HttpMethod;
use App\Exceptions\ControllerNotFoundException;
use App\Exceptions\InvalidRouteException;
use App\Routing\Dispatcher;
use App\Routing\Request;
use App\Routing\Router;
use PHPUnit\Framework\TestCase;
use Tests\Fixtures\Middlewares\BlockMiddleware;
use Tests\Fixtures\Middlewares\TraceMiddleware;

final class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $this->router = new Router(new Dispatcher('Tests\\Fixtures\\Controllers'));
    }

    private function dispatch(string $uri, HttpMethod $method = HttpMethod::Get): mixed
    {
        return $this->router->init(Request::fromGlobals(['REQUEST_METHOD' => $method->value, 'REQUEST_URI' => $uri]));
    }

    public function testDispatchesSimpleRouteIgnoringTrailingSlashAndQuery(): void
    {
        $this->router->add('/home/', 'GET', 'HomeController:index');

        self::assertSame('home', $this->dispatch('/home'));
        self::assertSame('home', $this->dispatch('/home/?a=1'));
    }

    public function testFallsBackToNotFoundHandler(): void
    {
        $this->router->add('/home', 'GET', 'HomeController:index');

        self::assertSame('not found', $this->dispatch('/missing'));
        self::assertSame('not found', $this->dispatch('/home', HttpMethod::Post));
    }

    public function testHeadRequestsMatchGetRoutes(): void
    {
        $this->router->add('/home', 'GET', 'HomeController:index');

        self::assertSame('home', $this->dispatch('/home', HttpMethod::Head));
    }

    public function testPassesWildcardsAsNamedArguments(): void
    {
        $this->router->add('/p/(:numeric)/(:alpha)', 'GET', 'HomeController:params', ['id', 'slug']);

        self::assertSame(['id' => 10, 'slug' => 'abc'], $this->dispatch('/p/10/abc'));
    }

    public function testGroupAppliesPrefixAndControllerNamespace(): void
    {
        $this->router->group(['prefix' => 'admin', 'controller' => 'admin'], function () {
            $this->add('/reports/(:numeric)', 'GET', 'ReportsController:show', ['reportId']);
        });

        self::assertSame('/admin/reports/(:numeric)', $this->router->routes[0]->path);
        self::assertSame('report 3', $this->dispatch('/admin/reports/3'));
    }

    public function testNestedGroupsMergeOptionsAndRestoreParent(): void
    {
        $this->router->group(['prefix' => 'api', 'middlewares' => [TraceMiddleware::class]], function (Router $router) {
            $router->group(['prefix' => 'v1'], function (Router $router) {
                $router->add('/home', 'GET', 'HomeController:index');
            });
            $router->add('/home', 'GET', 'HomeController:index');
        });
        $this->router->add('/home', 'GET', 'HomeController:index');

        self::assertSame(['/api/v1/home', '/api/home', '/home'], array_map(
            static fn ($route) => $route->path,
            $this->router->routes,
        ));
        self::assertSame('trace(home)', $this->dispatch('/api/v1/home'));
        self::assertSame('home', $this->dispatch('/home'));
    }

    public function testMiddlewaresAreAppendedToLastRoute(): void
    {
        $this->router->group(['middlewares' => [TraceMiddleware::class]], function () {
            $this->add('/open', 'GET', 'HomeController:index');
            $this->add('/closed', 'GET', 'HomeController:index')->middlewares([BlockMiddleware::class]);
        });

        self::assertSame('trace(home)', $this->dispatch('/open'));
        self::assertSame('trace(blocked)', $this->dispatch('/closed'));
    }

    public function testOptionsBeforeAddThrows(): void
    {
        $this->expectException(\LogicException::class);

        $this->router->middlewares(['auth']);
    }

    public function testUnknownGroupOptionThrows(): void
    {
        $this->expectException(InvalidRouteException::class);

        $this->router->group(['prefx' => 'typo'], fn () => null);
    }

    public function testInvalidHandlerThrows(): void
    {
        $this->expectException(InvalidRouteException::class);

        $this->router->add('/x', 'GET', 'HomeController');
    }

    public function testNotFoundHandlerIsValidated(): void
    {
        $this->expectException(InvalidRouteException::class);

        $this->router->notFoundHandler = 'invalid';
    }

    public function testMissingControllerThrows(): void
    {
        $this->router->add('/x', 'GET', 'MissingController:index');

        $this->expectException(ControllerNotFoundException::class);

        $this->dispatch('/x');
    }
}
