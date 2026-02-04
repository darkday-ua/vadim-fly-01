<?php

declare(strict_types=1);

namespace App\View;

class View
{
    public function __construct(private string $basePath)
    {
    }

    public function render(string $name, array $data = []): string
    {
        $path = $this->basePath . '/' . $name . '.php';
        if (!is_file($path)) {
            throw new \RuntimeException("View not found: $name");
        }
        extract($data, EXTR_SKIP);
        ob_start();
        include $path;
        return (string) ob_get_clean();
    }
}
