<?php

declare(strict_types=1);

namespace App\Support;

final class Router
{
    private array $routes = [];

    public function get(string $path, callable|array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function patch(string $path, callable|array $handler): void
    {
        $this->add('PATCH', $path, $handler);
    }

    public function delete(string $path, callable|array $handler): void
    {
        $this->add('DELETE', $path, $handler);
    }

    private function add(string $method, string $path, callable|array $handler): void
    {
        $pattern = preg_replace('#\{([^/]+)\}#', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        preg_match_all('#\{([^/]+)\}#', $path, $matches);
        $paramNames = $matches[1] ?? [];

        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'params' => $paramNames,
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $path): Response
    {
        $request = new Request($method, $path);

        foreach ($this->routes as $route) {
            if ($route['method'] !== strtoupper($method)) {
                continue;
            }

            if (!preg_match($route['pattern'], $path, $matches)) {
                continue;
            }

            $args = [$request];
            foreach ($route['params'] as $param) {
                $args[] = $matches[$param] ?? null;
            }

            try {
                $handler = $route['handler'];
                if (is_array($handler)) {
                    [$class, $action] = $handler;
                    $controller = new $class();
                    $result = $controller->$action(...$args);
                } else {
                    $result = $handler(...$args);
                }

                if ($result instanceof Response) {
                    return $result;
                }

                return Response::json($result ?? ['status' => 'ok']);
            } catch (\RuntimeException $e) {
                return $this->handleRuntimeException($e);
            } catch (\Throwable $e) {
                $message = env('APP_ENV', 'production') === 'production'
                    ? 'Server error'
                    : $e->getMessage();

                return Response::error($message, 500, 'server_error');
            }
        }

        return Response::error('Not found', 404, 'not_found');
    }

    private function handleRuntimeException(\RuntimeException $exception): Response
    {
        return match ($exception->getMessage()) {
            'unauthenticated' => Response::error('Unauthorized', 401, 'unauthorized'),
            'forbidden' => Response::error('Forbidden', 403, 'forbidden'),
            'csrf' => Response::error('Invalid CSRF token', 403, 'csrf'),
            default => Response::error('Bad request', 400, 'bad_request'),
        };
    }
}
