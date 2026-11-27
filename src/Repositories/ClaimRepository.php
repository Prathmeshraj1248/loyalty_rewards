<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ClaimRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    // ---- Transaction control: either every step below happens, or none of them ----
    public function begin(): void
    {
        $this->pdo->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function rollBack(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    /**
     * Finds a code by its hash AND locks that row until commit/rollback.
     * If two people scan the same code at the same moment, the second one waits here,
     * then sees status = 'claimed'.
     */
    public function findCodeForUpdate(string $hash): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, status, batch_id FROM product_codes WHERE code_hash = ? FOR UPDATE');
        $stmt->execute([$hash]);
        return $stmt->fetch() ?: null;
    }

    /** How many coins a code from this batch is worth (taken from the batch's product). */
    public function coinsForBatch(int $batchId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.coins_per_unit FROM batches b JOIN products p ON p.id = b.product_id WHERE b.id = ?'
        );
        $stmt->execute([$batchId]);
        return (int) $stmt->fetchColumn();
    }

    /** The status is re-checked inside the UPDATE. Returns true only if this call changed the row. */
    public function markClaimed(int $codeId, int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE product_codes SET status = 'claimed', claimed_by = ?, claimed_at = NOW()
             WHERE id = ? AND status = 'unused'"
        );
        $stmt->execute([$userId, $codeId]);
        return $stmt->rowCount() === 1;
    }

    /** Adds the coins to the ledger. point_ledger.product_code_id is UNIQUE, so one code can never earn twice. */
    public function addEarnEntry(int $userId, int $points, int $codeId): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO point_ledger (user_id, entry_type, points, product_code_id) VALUES (?, 'earn', ?, ?)"
        );
        $stmt->execute([$userId, $points, $codeId]);
    }

    /** Records every attempt, good or bad. A logging problem must never stop a customer's claim. */
    public function logAttempt(?int $userId, ?int $codeId, string $result, string $ip): void
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO claim_attempts (user_id, product_code_id, result, ip_address) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$userId, $codeId, $result, $ip]);
        } catch (\Throwable $e) {
            error_log('claim_attempts insert failed: ' . $e->getMessage());
        }
    }
        /** Failed attempts in the last 15 minutes: [by this user, by this IP]. */
    public function failureCounts(int $userId, string $ip): array
    {
        $base = "SELECT COUNT(*) FROM claim_attempts WHERE result IN ('invalid','already_claimed','blocked')
                 AND created_at >= (NOW() - INTERVAL 15 MINUTE) AND ";
        $byUser = $this->pdo->prepare($base . 'user_id = ?');
        $byUser->execute([$userId]);
        $byIp = $this->pdo->prepare($base . 'ip_address = ?');
        $byIp->execute([$ip]);
        return [(int) $byUser->fetchColumn(), (int) $byIp->fetchColumn()];
    }
}
