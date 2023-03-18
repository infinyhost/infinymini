<?php

namespace InfinyHost\InfinyMini\Services\Router;

use InfinyHost\InfinyMini\Exceptions\RouteAlreadyExistsException;

class Router
{
    private array $routes;
    public function __construct()
    {
        $this->routes = [];
    }

    /**
     * Add a route to the router
     * @param string $method
     * @param string $path
     * @param callable $callback
     * @return Route
     * @throws RouteAlreadyExistsException
     */
    public function add(string $method, string $path, $callback): Route
    {
        list($path, $method) = $this->formatRoute($path, $method);
        $name = $path . '/' . $method;

        // Check if route already exists
        if (isset($this->routes[$name])) {
            throw new RouteAlreadyExistsException( "Route with path '$name' already exists");
        }
        // Add route
        $this->routes[$name] = new Route($method, $path, $callback);
        return $this->routes[$name];
    }

    /**
     * Get the route for the given path
     * @param string $path
     * @param string $method
     * @return Route|null
     */
    public function getRoute(string $path, string $method): ?Route
    {
        list($path,$method) = $this->formatRoute($path, $method);

        // Check if route exists
        return $this->routes[$path . "/" . $method] ?? null;
    }

    private function formatRoute(string $path, string $method): array
    {
        // Convert to lowercase
        $path = strtolower($path);
        $method = strtolower($method);

        // Add leading slash
        if ($path == '' || $path[0] != '/' && $method != 'cli') {
            $path = '/' . $path;
        }

        if ($method == 'cli' && $path == "") {
            $path = 'help';
        }

        // Remove trailing slash
        if ($path != '/' && substr($path, -1) == '/') {
            $path = substr($path, 0, -1);
        }
        return [$path, $method];
    }

    /**
     * Get all routes
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Get all routes as an array
     * @return array
     */
    public function toArray(): array
    {
        $routes = [];
        foreach ($this->routes as $route) {
            $routes[] = [
                'method' => $route->getMethod(),
                'path' => $route->getPath(),
                'callback' => $route->getCallback()
            ];
        }
        return $routes;
    }

    /**
     * Route current request
     * @param string $path
     * @param string $method
     * @return mixed    The result of the callback
     */
    public function route(string $path, string $method)
    {
        $route = $this->getRoute($path, $method);
        if ($route == null) {
            return null;
        }
        if (strtolower($route->getMethod()) != strtolower($method)) {
            return null;
        }

        return $route->execute();
    }

    // Load routes from file

    /**
     * Load routes from file
     * @param string $file
     * @return void
     * @throws RouteAlreadyExistsException
     */
    public function loadFromFile(string $file): void
    {
        $routes = require $file;
        foreach ($routes as $route) {
            $this->add($route['method'], $route['path'], $route['callback']);
        }
    }
}