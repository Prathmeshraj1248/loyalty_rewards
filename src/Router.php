<?php
declare(strict_types=1);

namespace App;

use App\Http\Response;

final class Router
{
    /** @var array<string, array<string, callable>> routes[path][METHOD] = handler */
    private array $routes = [];

    // $basePath is the part of the URL before our routes, e.g. /loyalty-rewards/public
    public function __construct(private string $basePath = '')
    {
    }

    public function get(string $path, callable $handler): void
    {
        $this->routes[$path]['GET'] = $handler;
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes[$path]['POST'] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        if ($this->basePath !== '' && str_starts_with($path, $this->basePath)) {
            $path = substr($path, strlen($this->basePath));
        }
        $path = '/' . trim($path, '/');

        if (!isset($this->routes[$path])) {
            Response::json(['error' => 'Not found'], 404);
            return;
        }
        if (!isset($this->routes[$path][$method])) {
            Response::json(['error' => 'Method not allowed'], 405);
            return;
        }

        ($this->routes[$path][$method])();
    }
}