# php-routing-system

Mini roteador MVC em PHP 8.5, sem dependências de runtime.

## Requisitos

- PHP >= 8.5 (extensão `uri`, nativa desde o 8.5)
- Composer

## Uso

```bash
composer install
composer serve     # php -S localhost:8000 -t public
composer test      # PHPUnit
composer analyse   # PHPStan (nível 8)
```

Com nginx + php-fpm: `docker compose up` (porta 8000), ou use `docker/nginx.conf` num nginx local
trocando `fastcgi_pass` para `unix:/run/php/php8.5-fpm.sock` e o `root` para o diretório `public/`.

## Definindo rotas (`routes/web.php`)

```php
$router->add('/products', 'GET', 'ProductController:index');

$router->group(['prefix' => 'client', 'controller' => 'client', 'middlewares' => ['auth']], function (Router $router) {
    // GET /client/5/stores/7 -> App\Controllers\Client\StoresController::list(clientId: 5, storeId: 7)
    $router->add('/(:numeric)/stores/(:numeric)', 'GET', 'StoresController:list', ['clientId', 'storeId']);
    $router->add('/(:numeric)', 'GET', 'ClientController:show', ['clientId'])->middlewares(['admin']);
});
```

- **Wildcards:** `(:numeric)` (convertido para `int`), `(:alpha)` e `(:any)`.
- **Aliases:** nomeiam os wildcards, na ordem, e são passados como argumentos nomeados para a action. Sem alias, o parâmetro recebe o nome do segmento anterior (`/client/(:numeric)` → `$client`).
- **Grupos:** podem ser aninhados. A closure recebe o router como argumento (`$this` também funciona, mas as IDEs não reconhecem). `prefix` e `controller` (sub-namespace de `App\Controllers`) são concatenados, e `middlewares` são acumulados.
- **`->middlewares([...])` / `->options([...])`:** acrescentam opções à última rota adicionada.
- **Middlewares:** implementam `App\Contracts\MiddlewareInterface::handle(Request $request, Closure $next)` e podem ser referenciados por alias (`App\Enums\RouteMiddlewares`) ou pelo nome da classe. Para interromper a requisição, basta retornar um valor sem chamar `$next`.
- **Retorno das actions:** strings são exibidas como estão, e arrays/`JsonSerializable` são enviados como JSON.
- **404:** quando nenhuma rota casa, é chamado `$router->notFoundHandler` (padrão `NotFoundController:index`).
