<?php

namespace Core;

class Router
{
    private array $routes = [];
    private string $method;
    private string $uri;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        if ($this->uri === false) $this->uri = '/';
        $this->uri = preg_replace('#/index\.php$#', '', $this->uri);
    }

    public function add(string $method, string $pattern, $handler): void
    {
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $pattern);
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => '#^' . $pattern . '$#',
            'handler' => $handler,
        ];
    }

    public function get(string $pattern, $handler): void { $this->add('GET', $pattern, $handler); }
    public function post(string $pattern, $handler): void { $this->add('POST', $pattern, $handler); }
    public function put(string $pattern, $handler): void { $this->add('PUT', $pattern, $handler); }
    public function delete(string $pattern, $handler): void { $this->add('DELETE', $pattern, $handler); }
    public function any(string $pattern, $handler): void { $this->add('GET', $pattern, $handler); $this->add('POST', $pattern, $handler); }

    public function dispatch(): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] === $this->method && preg_match($route['pattern'], $this->uri, $matches)) {
                array_shift($matches);
                $handler = $route['handler'];
                if (is_array($handler) && isset($handler[0]) && is_string($handler[0])) {
                    $controller = new $handler[0]();
                    $method = $handler[1];
                    $reflection = new \ReflectionMethod($controller, $method);
                    $params = $reflection->getParameters();
                    $args = [];
                    foreach ($params as $i => $param) {
                        $type = $param->getType();
                        if ($type && $type->getName() === 'int') {
                            if ($i < count($matches)) {
                                if (!is_numeric($matches[$i])) {
                                    http_response_code(404);
                                    echo "404 — Not Found";
                                    return;
                                }
                                $args[] = (int)$matches[$i];
                            } elseif ($param->isDefaultValueAvailable()) {
                                $args[] = $param->getDefaultValue();
                            } else {
                                $args[] = 0;
                            }
                        } else {
                            $args[] = $i < count($matches) ? $matches[$i] : ($param->isDefaultValueAvailable() ? $param->getDefaultValue() : null);
                        }
                    }
                    call_user_func_array([$controller, $method], $args);
                    return;
                }
                call_user_func_array($handler, $matches);
                return;
            }
        }

        http_response_code(404);
        echo "404 — Страница не найдена";
    }

    public static function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    public static function back(): void
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? '/';
        self::redirect($ref);
    }
}
