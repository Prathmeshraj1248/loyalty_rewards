<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ProductRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function all(): array
    {
        return $this->pdo
            ->query('SELECT id, name, coins_per_unit, is_active FROM products ORDER BY is_active DESC, name')
            ->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, name, coins_per_unit, is_active FROM products WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $name, int $coins): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO products (name, coins_per_unit) VALUES (?, ?)');
        $stmt->execute([$name, $coins]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, string $name, int $coins): void
    {
        $stmt = $this->pdo->prepare('UPDATE products SET name = ?, coins_per_unit = ? WHERE id = ?');
        $stmt->execute([$name, $coins, $id]);
    }

    public function setActive(int $id, bool $active): void
    {
        $stmt = $this->pdo->prepare('UPDATE products SET is_active = ? WHERE id = ?');
        $stmt->execute([(int) $active, $id]);
    }
}