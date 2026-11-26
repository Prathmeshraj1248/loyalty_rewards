<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Http\View;
use App\Middleware\Auth;
use App\Repositories\LedgerRepository;
use App\Repositories\SettingsRepository;

final class CustomerController
{
    private const PER_PAGE = 15;

    public function dashboard(): void
    {
        $user   = Auth::requireRole('customer');
        $ledger = new LedgerRepository(Database::connection());
        $rate   = (float) (new SettingsRepository(Database::connection()))->get('rupees_per_coin', '1.00');
        $balance = $ledger->balance((int) $user['id']);

        View::render('customer/dashboard', [
            'title'   => 'Home',
            'nav'     => 'customer',
            'active'  => 'home',
            'user'    => $user,
            'balance' => $balance,
            'rupees'  => $balance * $rate,
            'recent'  => $ledger->page((int) $user['id'], 5, 0),
        ]);
    }

    public function history(): void
    {
        $user   = Auth::requireRole('customer');
        $ledger = new LedgerRepository(Database::connection());

        $total = $ledger->count((int) $user['id']);
        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $page  = min(max(1, (int) ($_GET['page'] ?? 1)), $pages); // clamp to a valid page

        View::render('customer/history', [
            'title'   => 'History',
            'nav'     => 'customer',
            'active'  => 'history',
            'rows'    => $ledger->page((int) $user['id'], self::PER_PAGE, ($page - 1) * self::PER_PAGE),
            'page'    => $page,
            'pages'   => $pages,
        ]);
    }
}