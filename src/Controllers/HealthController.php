<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Http\Response;
use PDOException;

final class HealthController
{
    public function check(): void
    {
        try {
            $rows = Database::connection()
                ->query('SELECT setting_key, setting_value FROM settings')
                ->fetchAll();

            Response::json([
                'status'   => 'ok',
                'database' => 'connected',
                'settings' => array_column($rows, 'setting_value', 'setting_key'),
            ]);
        } catch (PDOException $e) {
            // Log the real error privately; never show database details to the visitor.
            error_log('Health check failed: ' . $e->getMessage());
            Response::json(['status' => 'error', 'database' => 'unavailable'], 500);
        }
    }
}