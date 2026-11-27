<?php
require __DIR__ . '/env.php';
require_once __DIR__ . '/DbTestCase.php';

// Rebuild the throwaway test database from schema.sql. The name is swapped, so the real database is never touched.
$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('DROP DATABASE IF EXISTS loyalty_rewards_test');
$stmt = $pdo->query(str_replace('loyalty_rewards', 'loyalty_rewards_test', file_get_contents(__DIR__ . '/../database/schema.sql')));
while ($stmt->nextRowset()) {
    // walking through every statement makes a failure in any of them throw
}
$tables = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'loyalty_rewards_test'")->fetchColumn();
if ($tables < 8) {
    throw new RuntimeException("Test database was not built correctly ($tables tables).");
}