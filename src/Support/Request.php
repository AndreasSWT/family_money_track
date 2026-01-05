<?php

declare(strict_types=1);

namespace App\Support;

final class Request
{
    private string $method;
    private string $path;
    private array $query;
    private array $body;
    private array $headers;

    public function __construct(string $method, string $path)
    {
        $this->method = strtoupper($method);
        $this->path = $path;
        $this->query = $_GET ?? [];
        $this->body = $this->parseBody();
        $this->headers = $this->parseHeaders();
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->body)) {
            return $this->body[$key];
        }

        if (array_key_exists($key, $this->query)) {
            return $this->query[$key];
        }

        return $default;
    }

    public function all(): array
    {
        return $this->body + $this->query;
    }

    public function header(string $name): ?string
    {
        $lookup = strtolower($name);
        return $this->headers[$lookup] ?? null;
    }

    private function parseBody(): array
    {
        $raw = file_get_contents('php://input');
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (str_contains($contentType, 'application/json')) {
            $decoded = json_decode($raw ?: '', true);
            return is_array($decoded) ? $decoded : [];
        }

        if (is_array($_POST)) {
            return $_POST;
        }

        return [];
    }

    private function parseHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = strtolower(str_replace('_', '-', substr($key, 5)));
                $headers[$name] = $value;
            }
        }

        return $headers;
    }
}
