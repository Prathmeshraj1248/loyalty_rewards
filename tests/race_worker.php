<?php
// Usage: php race_worker.php USER_ID CODE START_TIME
use App\Config\Database;
use App\Repositories\ClaimRepository;
use App\Services\ClaimService;
use App\Services\CodeService;

require __DIR__ . '/env.php';

[, $userId, $code, $start] = $argv;
$service = new ClaimService(new ClaimRepository(Database::connection()), new CodeService()); // connect BEFORE waiting

while (microtime(true) < (float) $start) {
    usleep(200); // everyone fires at the same moment
}

try {
    $r = $service->claim((int) $userId, $code, '127.0.0.1');
    echo ($r['ok'] ? 'OK ' : 'NO ') . $r['message'];
} catch (Throwable $e) {
    echo 'ERR ' . $e->getMessage();
}