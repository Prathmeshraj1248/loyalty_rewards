<?php
declare(strict_types=1);

use App\Config\Database;
use App\Repositories\BatchRepository;
use App\Repositories\ClaimRepository;
use App\Repositories\LedgerRepository;
use App\Services\BatchService;
use App\Services\ClaimService;
use App\Services\CodeService;
use PHPUnit\Framework\TestCase;

abstract class DbTestCase extends TestCase
{
    protected PDO $pdo;
    protected int $adminId;
    protected int $productId;

    protected function setUp(): void
    {
        $this->pdo = Database::connection();
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach (['claim_attempts', 'point_ledger', 'redemption_requests', 'product_codes', 'batches', 'products', 'users'] as $table) {
            $this->pdo->exec("TRUNCATE TABLE $table");
        }
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

        $this->adminId = $this->makeUser('admin');
        $this->pdo->exec("INSERT INTO products (name, coins_per_unit) VALUES ('Test Soap', 10)");
        $this->productId = (int) $this->pdo->lastInsertId();
    }

    protected function makeUser(string $role = 'customer'): int
    {
        static $n = 0;
        $n++;
        $this->pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)')
            ->execute(["User $n", "u$n@test.local", 'x', $role]);
        return (int) $this->pdo->lastInsertId();
    }

    /** Creates real hashed codes through BatchService and returns the plain codes. */
    protected function makeCodes(int $quantity = 1): array
    {
        $service = new BatchService($this->pdo, new BatchRepository($this->pdo), new CodeService());
        return $service->generate($this->productId, $quantity, $this->adminId)['codes'];
    }

    protected function claimService(): ClaimService
    {
        return new ClaimService(new ClaimRepository($this->pdo), new CodeService());
    }

    protected function balance(int $userId): int
    {
        return (new LedgerRepository($this->pdo))->balance($userId);
    }
}