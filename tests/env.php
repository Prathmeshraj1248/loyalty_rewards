<?php
// Test settings: a separate database, so tests can never touch real data.
// If your MySQL root user has a password, put it in DB_PASS below.
require __DIR__ . '/../vendor/autoload.php';

$file = tempnam(sys_get_temp_dir(), 'env');
file_put_contents($file, "DB_HOST=127.0.0.1\nDB_PORT=3306\nDB_NAME=loyalty_rewards_test\nDB_USER=root\nDB_PASS=\nCODE_HASH_SECRET=test-secret-not-real\n");
\App\Config\Env::load($file);
unlink($file);