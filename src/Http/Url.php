<?php
declare(strict_types=1);

namespace App\Http;

final class Url
{
    private static string $base = '';

    public static function setBase(string $base): void
    {
        self::$base = rtrim($base, '/');
    }

    public static function to(string $path): string
    {
        return self::$base . $path;
    }

    // Only ever called with fixed internal paths, never user input (prevents open redirects).
    public static function redirect(string $path): never
    {
        header('Location: ' . self::to($path));
        exit;
    }
}