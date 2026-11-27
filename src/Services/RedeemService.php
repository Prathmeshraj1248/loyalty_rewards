<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\LedgerRepository;
use App\Repositories\SettingsRepository;
use PDO;

final class RedeemService
{
    public function __construct(private PDO $pdo)
    {
    }

    /** Returns an error message, or null on success. */
    public function request(int $userId, int $coins): ?string
    {
        $settings = new SettingsRepository($this->pdo);
        $min = (int) $settings->get('min_redeem_coins', '100');
        if ($coins < $min) {
            return "Minimum redemption is $min coins.";
        }
        $rupees = round($coins * (float) $settings->get('rupees_per_coin', '1.00'), 2);

        $this->pdo->beginTransaction();
        try {
            // Lock this user's row: a simultaneous second request waits until we commit.
            $lock = $this->pdo->prepare('SELECT id FROM users WHERE id = ? FOR UPDATE');
            $lock->execute([$userId]);
            $lock->fetchColumn();

            if ($coins > (new LedgerRepository($this->pdo))->balance($userId)) {
                $this->pdo->rollBack();
                return 'You do not have enough coins.';
            }

            $this->pdo->prepare('INSERT INTO redemption_requests (user_id, coins, rupees) VALUES (?, ?, ?)')
                ->execute([$userId, $coins, $rupees]);
            $id = (int) $this->pdo->lastInsertId();

            // The "hold": the coins leave the balance immediately.
            $this->pdo->prepare("INSERT INTO point_ledger (user_id, entry_type, points, redemption_id) VALUES (?, 'redeem_hold', ?, ?)")
                ->execute([$userId, -$coins, $id]);

            $this->pdo->commit();
            return null;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function approve(int $id, int $adminId): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE redemption_requests SET status = 'approved', reviewed_by = ?, reviewed_at = NOW()
             WHERE id = ? AND status = 'pending'"
        );
        $stmt->execute([$adminId, $id]);
        return $stmt->rowCount() === 1; // false if it was already handled
    }

    public function reject(int $id, int $adminId): bool
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE redemption_requests SET status = 'rejected', reviewed_by = ?, reviewed_at = NOW()
                 WHERE id = ? AND status = 'pending'"
            );
            $stmt->execute([$adminId, $id]);
            if ($stmt->rowCount() !== 1) {
                $this->pdo->rollBack();
                return false;
            }

            // Give the held coins back.
            $this->pdo->prepare(
                "INSERT INTO point_ledger (user_id, entry_type, points, redemption_id)
                 SELECT user_id, 'redeem_refund', coins, id FROM redemption_requests WHERE id = ?"
            )->execute([$id]);

            $this->pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
}