<?php

declare(strict_types=1);

namespace App\Http;

class Router
{
    /** @var array<string, array<string, callable>> method => path => handler */
    private array $routes = [];

    /** @var array<string, bool> path => requires auth */
    private array $protected = [];

    public function get(string $path, callable $handler, bool $auth = false): self
    {
        $this->routes['GET'][$this->normalize($path)] = $handler;
        if ($auth) {
            $this->protected[$this->normalize($path)] = true;
        }
        return $this;
    }

    public function post(string $path, callable $handler, bool $auth = false): self
    {
        $this->routes['POST'][$this->normalize($path)] = $handler;
        if ($auth) {
            $this->protected[$this->normalize($path)] = true;
        }
        return $this;
    }

    public function match(Request $request): ?array
    {
        $method = $request->method;
        $path = $this->normalize($request->path());
        $routes = $this->routes[$method] ?? [];

        if (isset($routes[$path])) {
            return [$routes[$path], $this->protected[$path] ?? false];
        }

        return null;
    }

    private function normalize(string $path): string
    {
        return '/' . trim($path, '/') ?: '/';
    }
}
