<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\BatchRepository;
use PDO;

final class BatchService
{
    public function __construct(
        private PDO $pdo,
        private BatchRepository $batches,
        private CodeService $codes
    ) {
    }

    /**
     * Creates a batch and its hashed codes in ONE transaction: either everything is saved or nothing is.
     * @return array{batch_id: int, codes: string[]} the plain codes, which exist only in this return value
     */
    public function generate(int $productId, int $quantity, int $adminId): array
    {
        // Keyed by normalized code so a (practically impossible) duplicate is simply replaced.
        $unique = [];
        while (count($unique) < $quantity) {
            $formatted = $this->codes->generate();
            $unique[$this->codes->normalize($formatted)] = $formatted;
        }

        $this->pdo->beginTransaction();
        try {
            $batchId = $this->batches->create($productId, $quantity, $adminId);
            foreach (array_chunk(array_keys($unique), 250) as $chunk) {
                $this->batches->insertCodes($batchId, array_map([$this->codes, 'hash'], $chunk));
            }
            $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }

        return ['batch_id' => $batchId, 'codes' => array_values($unique)];
    }
}