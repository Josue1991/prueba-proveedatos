<?php

class Router
{
    private array $routes = [];

    public function addRoute(string $method, string $pattern, string $controller, string $action): void
    {
        $this->routes[] = compact('method', 'pattern', 'controller', 'action');
    }

    public function dispatch(string $method, string $uri): void
    {
        // Strip query string and leading slash
        $uri = strtok($uri, '?');
        $uri = '/' . ltrim($uri, '/');

        foreach ($this->routes as $route) {
            if (strtoupper($route['method']) !== strtoupper($method)) {
                continue;
            }

            $params = [];
            $regex  = $this->patternToRegex($route['pattern'], $paramNames);

            if (preg_match($regex, $uri, $matches)) {
                array_shift($matches);
                $params = array_combine($paramNames, $matches);

                $controller = new $route['controller']();
                call_user_func_array([$controller, $route['action']], $params);
                return;
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'Ruta no encontrada']);
    }

    private function patternToRegex(string $pattern, &$paramNames): string
    {
        $paramNames = [];

        $regex = preg_replace_callback('/\{(\w+)\}/', function ($m) use (&$paramNames) {
            $paramNames[] = $m[1];
            return '(\d+)';
        }, $pattern);

        return '#^' . $regex . '$#';
    }
}
