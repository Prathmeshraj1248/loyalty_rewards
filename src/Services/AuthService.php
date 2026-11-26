<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use PDOException;

final class AuthService
{
    private const BAD_LOGIN = 'Incorrect email or password.';

    public function __construct(private UserRepository $users)
    {
    }

    /** @return array{errors: string[], user_id: ?int} */
    public function register(string $name, string $email, string $password, string $confirm): array
    {
        $name  = trim($name);
        $email = strtolower(trim($email));
        $errors = [];

        $nameLength = mb_strlen($name);
        if ($nameLength < 2 || $nameLength > 100) {
            $errors[] = 'Name must be between 2 and 100 characters.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }
        if (strlen($password) > 72) { // bcrypt only uses the first 72 bytes
            $errors[] = 'Password must be at most 72 characters.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }
        if ($errors) {
            return ['errors' => $errors, 'user_id' => null];
        }

        try {
            // The role is fixed here. It is NEVER read from the form.
            $id = $this->users->create($name, $email, password_hash($password, PASSWORD_DEFAULT), 'customer');
            return ['errors' => [], 'user_id' => $id];
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') { // duplicate key: the UNIQUE email constraint fired
                return ['errors' => ['This email is already registered.'], 'user_id' => null];
            }
            throw $e;
        }
    }

    /** @return array{user: ?array, error: ?string} */
    public function login(string $email, string $password): array
    {
        $user = $this->users->findByEmail(strtolower(trim($email)));

        if ($user === null || strlen($password) > 72) {
            // Do similar work as a real check so response time does not reveal whether the email exists.
            password_hash($password, PASSWORD_DEFAULT);
            return ['user' => null, 'error' => self::BAD_LOGIN];
        }
        if (!password_verify($password, $user['password_hash'])) {
            return ['user' => null, 'error' => self::BAD_LOGIN];
        }
        if ($user['status'] !== 'active') {
            return ['user' => null, 'error' => 'This account has been disabled. Please contact support.'];
        }

        return ['user' => $user, 'error' => null];
    }
}