<?php
declare(strict_types=1);

namespace App\Repositories;

use DateTimeImmutable;
use PDO;

final class StatsRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function summary(): array
    {
        $codes = $this->pdo->query(
            "SELECT COUNT(*) AS total,
                    COALESCE(SUM(status = 'unused'), 0)  AS unused,
                    COALESCE(SUM(status = 'claimed'), 0) AS claimed,
                    COALESCE(SUM(status = 'blocked'), 0) AS blocked
             FROM product_codes"
        )->fetch();

        return [
            'customers'    => (int) $this->pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn(),
            'codes_total'  => (int) $codes['total'],
            'codes_unused' => (int) $codes['unused'],
            'codes_claimed' => (int) $codes['claimed'],
            'codes_blocked' => (int) $codes['blocked'],
            'coins_issued' => (int) $this->pdo->query("SELECT COALESCE(SUM(points), 0) FROM point_ledger WHERE entry_type = 'earn'")->fetchColumn(),
            'failed_7d'    => (int) $this->pdo->query("SELECT COUNT(*) FROM claim_attempts WHERE result <> 'success' AND created_at >= (NOW() - INTERVAL 7 DAY)")->fetchColumn(),
        ];
    }

    /** Last 7 days, with zeros for days that had no attempts. */
    public function dailyClaims(): array
    {
        $rows = $this->pdo->query(
            "SELECT DATE(created_at) AS d,
                    SUM(result = 'success')  AS ok,
                    SUM(result <> 'success') AS failed
             FROM claim_attempts
             WHERE created_at >= (CURDATE() - INTERVAL 6 DAY)
             GROUP BY DATE(created_at)"
        )->fetchAll();
        $byDay = array_column($rows, null, 'd');

        // Use the database's own "today" so PHP and MySQL time zones can never disagree.
        $today = new DateTimeImmutable((string) $this->pdo->query('SELECT CURDATE()')->fetchColumn());
        $out = ['labels' => [], 'success' => [], 'failed' => [], 'hasData' => $rows !== []];
        for ($i = 6; $i >= 0; $i--) {
            $day = $today->modify("-$i day");
            $key = $day->format('Y-m-d');
            $out['labels'][]  = $day->format('d M');
            $out['success'][] = (int) ($byDay[$key]['ok'] ?? 0);
            $out['failed'][]  = (int) ($byDay[$key]['failed'] ?? 0);
        }
        return $out;
    }

    public function topCustomers(int $limit): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT u.name, u.email, SUM(l.points) AS balance
             FROM users u JOIN point_ledger l ON l.user_id = u.id
             WHERE u.role = 'customer'
             GROUP BY u.id
             ORDER BY balance DESC
             LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function recentAttempts(int $limit): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT a.result, a.ip_address, a.created_at, u.email
             FROM claim_attempts a LEFT JOIN users u ON u.id = a.user_id
             ORDER BY a.id DESC LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}