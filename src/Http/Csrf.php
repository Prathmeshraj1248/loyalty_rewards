<?php
declare(strict_types=1);

namespace App\Http;

final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . self::token() . '">';
    }

    public static function verify(?string $submitted): bool
    {
        // hash_equals compares in constant time, so timing cannot leak the token
        return hash_equals(self::token(), (string) $submitted);
    }
}
