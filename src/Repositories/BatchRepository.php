<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class BatchRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(int $productId, int $quantity, int $adminId): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO batches (product_id, quantity, created_by) VALUES (?, ?, ?)');
        $stmt->execute([$productId, $quantity, $adminId]);
        return (int) $this->pdo->lastInsertId();
    }

    /** @param string[] $hashes one 64-character HMAC hash per code */
    public function insertCodes(int $batchId, array $hashes): void
    {
        $placeholders = implode(',', array_fill(0, count($hashes), '(?, ?)'));
        $params = [];
        foreach ($hashes as $hash) {
            $params[] = $batchId;
            $params[] = $hash;
        }
        $this->pdo->prepare("INSERT INTO product_codes (batch_id, code_hash) VALUES $placeholders")->execute($params);
    }

    public function recent(int $limit = 50): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT b.id, b.quantity, b.created_at, p.name AS product_name, p.coins_per_unit,
                    SUM(c.status = 'unused')  AS unused,
                    SUM(c.status = 'claimed') AS claimed,
                    SUM(c.status = 'blocked') AS blocked
             FROM batches b
             JOIN products p      ON p.id = b.product_id
             JOIN product_codes c ON c.batch_id = b.id
             GROUP BY b.id, p.name, p.coins_per_unit
             ORDER BY b.id DESC
             LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}