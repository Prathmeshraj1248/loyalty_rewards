<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class UserRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $name, string $email, string $passwordHash, string $role): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$name, $email, $passwordHash, $role]);
        return (int) $this->pdo->lastInsertId();
    }
        /** Customers with their balance and number of claims. Search matches name or email. */
    public function customers(string $search = '', int $limit = 100): array
    {
        $sql = "SELECT u.id, u.name, u.email, u.status, u.created_at,
                       COALESCE(SUM(l.points), 0)               AS balance,
                       COALESCE(SUM(l.entry_type = 'earn'), 0)  AS claims
                FROM users u
                LEFT JOIN point_ledger l ON l.user_id = u.id
                WHERE u.role = 'customer'";
        if ($search !== '') {
            // Real prepared statements cannot reuse one named placeholder, so there are two (:q1, :q2).
            $sql .= ' AND (u.email LIKE :q1 OR u.name LIKE :q2)';
        }
        $sql .= ' GROUP BY u.id ORDER BY u.id DESC LIMIT :lim';

        $stmt = $this->pdo->prepare($sql);
        if ($search !== '') {
            $like = '%' . addcslashes($search, '%_\\') . '%';
            $stmt->bindValue(':q1', $like);
            $stmt->bindValue(':q2', $like);
        }
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function setStatus(int $id, string $status): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
    }
}