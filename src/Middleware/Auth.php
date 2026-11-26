<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Config\Database;
use App\Http\Session;
use App\Http\Url;
use App\Http\View;
use App\Repositories\UserRepository;

final class Auth
{
    /** Returns the logged-in user, or redirects to the login page. */
    public static function requireLogin(): array
    {
        $id = Session::userId();
        $user = $id !== null ? (new UserRepository(Database::connection()))->findById($id) : null;

        // The user is re-read from the database on EVERY request, so blocking
        // an account or changing a role takes effect immediately.
        if ($user === null || $user['status'] !== 'active') {
            Session::logout();
            Session::flash('error', 'Please log in to continue.');
            Url::redirect('/login');
        }
        return $user;
    }

    /** Returns the user only if they have the required role, otherwise shows 403. */
    public static function requireRole(string $role): array
    {
        $user = self::requireLogin();
        if ($user['role'] !== $role) {
            View::render('errors/403', ['title' => 'Access denied'], 403);
            exit;
        }
        return $user;
    }
}