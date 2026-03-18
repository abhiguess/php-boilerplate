<?php

class Router
{
    private static array $routes = [];

    public static function get(string $path, array|callable $handler): void
    {
        self::$routes[] = ['GET', $path, $handler];
    }

    public static function post(string $path, array|callable $handler): void
    {
        self::$routes[] = ['POST', $path, $handler];
    }

    public static function put(string $path, array|callable $handler): void
    {
        self::$routes[] = ['PUT', $path, $handler];
    }

    public static function delete(string $path, array|callable $handler): void
    {
        self::$routes[] = ['DELETE', $path, $handler];
    }

    public static function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];

        // Support _method override for PUT/DELETE from forms
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        $uri = self::getUri();

        foreach (self::$routes as [$routeMethod, $routePath, $handler]) {
            if ($routeMethod !== $method) {
                continue;
            }

            $params = self::match($routePath, $uri);

            if ($params !== false) {
                self::call($handler, $params);
                return;
            }
        }

        // 404
        http_response_code(404);
        if (self::isJson()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Not Found']);
        } else {
            echo '<h1>404 - Not Found</h1>';
        }
    }

    private static function getUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Remove base path (for subfolder installs like /php-boilerplate)
        $basePath = parse_url(env('APP_URL', ''), PHP_URL_PATH) ?: '';
        if ($basePath && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }

        // Remove query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        return '/' . trim($uri, '/');
    }

    /**
     * Match route pattern against URI. Returns params array or false.
     * Supports {param} placeholders.
     */
    private static function match(string $pattern, string $uri): array|false
    {
        $pattern = '/' . trim($pattern, '/');
        $uri = '/' . trim($uri, '/');

        // Exact match
        if ($pattern === $uri) {
            return [];
        }

        // Convert {param} to regex
        $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $uri, $matches)) {
            return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        }

        return false;
    }

    private static function call(array|callable $handler, array $params): void
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }

        [$class, $method] = $handler;
        $controller = new $class();
        call_user_func_array([$controller, $method], $params);
    }

    private static function isJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return str_contains($accept, 'application/json');
    }
}
