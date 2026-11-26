<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Http\Csrf;
use App\Http\Session;
use App\Http\Url;
use App\Http\View;
use App\Repositories\UserRepository;
use App\Services\AuthService;

final class AuthController
{
    private AuthService $auth;
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository(Database::connection());
        $this->auth  = new AuthService($this->users);
    }

    public function home(): void
    {
        $this->redirectIfLoggedIn();
        Url::redirect('/login');
    }

    public function showRegister(): void
    {
        $this->redirectIfLoggedIn();
        View::render('auth/register', ['title' => 'Create account', 'errors' => [], 'old' => []]);
    }

    public function register(): void
    {
        $this->requireValidCsrf('/register');

        $old = [
            'name'  => (string) ($_POST['name'] ?? ''),
            'email' => (string) ($_POST['email'] ?? ''),
        ];
        $result = $this->auth->register(
            $old['name'],
            $old['email'],
            (string) ($_POST['password'] ?? ''),
            (string) ($_POST['password_confirm'] ?? '')
        );

        if ($result['errors']) {
            View::render('auth/register', ['title' => 'Create account', 'errors' => $result['errors'], 'old' => $old], 422);
            return;
        }

        Session::login($result['user_id']);
        Session::flash('success', 'Welcome! Your account has been created.');
        Url::redirect('/dashboard');
    }

    public function showLogin(): void
    {
        $this->redirectIfLoggedIn();
        View::render('auth/login', ['title' => 'Log in', 'error' => null, 'old' => []]);
    }

    public function login(): void
    {
        $this->requireValidCsrf('/login');

        $email  = (string) ($_POST['email'] ?? '');
        $result = $this->auth->login($email, (string) ($_POST['password'] ?? ''));

        if ($result['user'] === null) {
            View::render('auth/login', ['title' => 'Log in', 'error' => $result['error'], 'old' => ['email' => $email]], 401);
            return;
        }

        Session::login((int) $result['user']['id']);
        Url::redirect($result['user']['role'] === 'admin' ? '/admin' : '/dashboard');
    }

    public function logout(): void
    {
        $this->requireValidCsrf('/login');
        Session::logout();
        Session::flash('success', 'You have been logged out.');
        Url::redirect('/login');
    }

    private function redirectIfLoggedIn(): void
    {
        $id = Session::userId();
        $user = $id !== null ? $this->users->findById($id) : null;
        if ($user !== null && $user['status'] === 'active') {
            Url::redirect($user['role'] === 'admin' ? '/admin' : '/dashboard');
        }
    }

    private function requireValidCsrf(string $backTo): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Session::flash('error', 'Your form expired. Please try again.');
            Url::redirect($backTo);
        }
    }
}
