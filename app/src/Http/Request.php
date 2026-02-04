<?php

declare(strict_types=1);

namespace App\Http;

class Request
{
    public function __construct(
        public readonly string $method,
        public readonly string $uri,
        public readonly array $query,
        public readonly array $body,
        public readonly array $headers,
    ) {
    }

    public static function fromGlobals(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        parse_str(parse_url($uri, PHP_URL_QUERY) ?: '', $query);

        $body = [];
        if ($method === 'POST' && ($_SERVER['CONTENT_TYPE'] ?? '') === 'application/x-www-form-urlencoded') {
            $body = $_POST;
        }

        $headers = getallheaders() ?: [];

        return new self($method, $path, $query, $body, $headers);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $this->body[$key] ?? $default;
    }

    public function path(): string
    {
        return '/' . trim($this->uri, '/') ?: '/';
    }
}
