<?php
declare(strict_types=1);

namespace App\Http;

final class Session
{
    private const IDLE_TIMEOUT = 1800; // 30 minutes

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

        ini_set('session.use_strict_mode', '1'); // refuse session IDs the server did not create
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'httponly' => true,       // JavaScript cannot read the cookie
            'secure'   => $isHttps,   // HTTPS only when the site uses HTTPS
            'samesite' => 'Lax',      // limits cross-site requests
        ]);
        session_name('loyalty_sid');
        session_start();

        $now = time();
        if (isset($_SESSION['last_activity']) && $now - $_SESSION['last_activity'] > self::IDLE_TIMEOUT) {
            $_SESSION = [];
            session_regenerate_id(true);
            self::flash('error', 'Your session expired. Please log in again.');
        }
        $_SESSION['last_activity'] = $now;
    }

    public static function login(int $userId): void
    {
        session_regenerate_id(true); // new ID after login stops session fixation
        unset($_SESSION['csrf']);    // fresh CSRF token for the new session
        $_SESSION['user_id'] = $userId;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
    }

    public static function userId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    public static function pullFlash(): ?array
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }
}