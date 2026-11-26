<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class LedgerRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    // Balance is never stored. It is always the sum of the ledger rows.
    public function balance(int $userId): int
    {
        $stmt = $this->pdo->prepare('SELECT COALESCE(SUM(points), 0) FROM point_ledger WHERE user_id = ?');
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public function count(int $userId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM point_ledger WHERE user_id = ?');
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public function page(int $userId, int $limit, int $offset): array
    {
        // LIMIT/OFFSET must be bound as integers when real prepared statements are used.
        $stmt = $this->pdo->prepare(
            'SELECT entry_type, points, created_at FROM point_ledger
             WHERE user_id = :uid ORDER BY id DESC LIMIT :lim OFFSET :off'
        );
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}