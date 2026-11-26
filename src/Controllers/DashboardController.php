<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Http\View;
use App\Middleware\Auth;
use App\Repositories\StatsRepository;

final class DashboardController
{
    public function admin(): void
    {
        $user  = Auth::requireRole('admin');
        $stats = new StatsRepository(Database::connection());

        View::render('admin/dashboard', [
            'title' => 'Dashboard', 'nav' => 'admin', 'active' => 'home', 'user' => $user,
            'summary'  => $stats->summary(),
            'daily'    => $stats->dailyClaims(),
            'top'      => $stats->topCustomers(5),
            'attempts' => $stats->recentAttempts(10),
        ]);
    }
}