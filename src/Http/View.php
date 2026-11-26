<?php
declare(strict_types=1);

namespace App\Http;

final class View
{
    public static function render(string $view, array $data = [], int $status = 200): void
    {
        http_response_code($status);

        // Helpers available inside every view: $e escapes output (stops XSS), $url builds links.
        $e   = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        $url = static fn (string $path): string => Url::to($path);

        extract($data, EXTR_SKIP);

        ob_start();
        require __DIR__ . '/../../views/' . $view . '.php';
        $content = ob_get_clean();

        require __DIR__ . '/../../views/layout.php';
    }
}