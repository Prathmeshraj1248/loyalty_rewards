<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class CodeRepository
{
    private const DETAIL_SQL =
        "SELECT c.id, c.status, c.claimed_at, c.created_at, c.batch_id,
                p.name AS product_name, p.coins_per_unit,
                u.name AS claimed_name, u.email AS claimed_email
         FROM product_codes c
         JOIN batches b  ON b.id = c.batch_id
         JOIN products p ON p.id = b.product_id
         LEFT JOIN users u ON u.id = c.claimed_by";

    public function __construct(private PDO $pdo)
    {
    }

    public function findDetailByHash(string $hash): ?array
    {
        $stmt = $this->pdo->prepare(self::DETAIL_SQL . ' WHERE c.code_hash = ?');
        $stmt->execute([$hash]);
        return $stmt->fetch() ?: null;
    }

    public function findDetailById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(self::DETAIL_SQL . ' WHERE c.id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // The status is checked inside the UPDATE itself, so a code that was claimed a moment ago is never blocked.
    public function block(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE product_codes SET status = 'blocked' WHERE id = ? AND status = 'unused'");
        $stmt->execute([$id]);
        return $stmt->rowCount() === 1;
    }

    public function unblock(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE product_codes SET status = 'unused' WHERE id = ? AND status = 'blocked' AND claimed_by IS NULL");
        $stmt->execute([$id]);
        return $stmt->rowCount() === 1;
    }

    /** Returns how many codes were blocked. Already claimed codes are untouched. */
    public function blockBatch(int $batchId): int
    {
        $stmt = $this->pdo->prepare("UPDATE product_codes SET status = 'blocked' WHERE batch_id = ? AND status = 'unused'");
        $stmt->execute([$batchId]);
        return $stmt->rowCount();
    }
}