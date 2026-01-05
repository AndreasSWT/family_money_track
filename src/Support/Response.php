<?php

declare(strict_types=1);

namespace App\Support;

final class Response
{
    private int $status;
    private array $headers;
    private mixed $data;

    public function __construct(mixed $data, int $status = 200, array $headers = [])
    {
        $this->data = $data;
        $this->status = $status;
        $this->headers = $headers;
    }

    public static function json(mixed $data, int $status = 200): self
    {
        return new self($data, $status, ['Content-Type' => 'application/json']);
    }

    public static function error(string $message, int $status = 400, ?string $code = null): self
    {
        $payload = ['error' => $message];
        if ($code !== null) {
            $payload['code'] = $code;
        }

        return self::json($payload, $status);
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo json_encode($this->data, JSON_UNESCAPED_SLASHES);
    }
}
