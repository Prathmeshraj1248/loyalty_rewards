<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Http\Csrf;
use App\Http\Session;
use App\Http\Url;
use App\Http\View;
use App\Middleware\Auth;
use App\Repositories\StatsRepository;
use App\Services\InsightService;

final class DashboardController
{
    public function admin(): void
    {
        $user  = Auth::requireRole('admin');
        $stats = new StatsRepository(Database::connection());

        View::render('admin/dashboard', [
            'title' => 'Dashboard', 'nav' => 'admin', 'active' => 'home', 'user' => $user,
            'summary'   => $stats->summary(),
            'daily'     => $stats->dailyClaims(),
            'top'       => $stats->topCustomers(5),
            'attempts'  => $stats->recentAttempts(10),
            'flags'     => (new InsightService(Database::connection()))->flags(),
            'aiSummary' => Session::get('ai_summary'),
        ]);
    }

    public function insights(): void
    {
        Auth::requireRole('admin');
        Csrf::enforce('/admin');
        $svc = new InsightService(Database::connection());
        Session::put('ai_summary', $svc->summarise($svc->flags()));
        Url::redirect('/admin');
    }
}