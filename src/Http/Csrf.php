<?php
declare(strict_types=1);

namespace App\Http;

final class Csrf
{
        // Call at the top of every POST handler. Redirects back if the token is missing or wrong.
    public static function enforce(string $backTo): void
    {
        $token = $_POST['csrf_token'] ?? null;
        if (!self::verify(is_string($token) ? $token : null)) {
            Session::flash('error', 'Your form expired. Please try again.');
            Url::redirect($backTo);
        }
    }
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
