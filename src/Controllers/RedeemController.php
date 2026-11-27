<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Http\Csrf;
use App\Http\Session;
use App\Http\Url;
use App\Http\View;
use App\Middleware\Auth;
use App\Repositories\LedgerRepository;
use App\Repositories\SettingsRepository;
use App\Services\RedeemService;

final class RedeemController
{
    // ---- customer ----
    public function show(): void
    {
        $user = Auth::requireRole('customer');
        $pdo  = Database::connection();
        $s    = new SettingsRepository($pdo);

        $stmt = $pdo->prepare('SELECT coins, rupees, status, created_at FROM redemption_requests WHERE user_id = ? ORDER BY id DESC LIMIT 20');
        $stmt->execute([$user['id']]);

        View::render('customer/redeem', [
            'title' => 'Redeem', 'nav' => 'customer', 'active' => 'redeem',
            'balance'  => (new LedgerRepository($pdo))->balance((int) $user['id']),
            'min'      => (int) $s->get('min_redeem_coins', '100'),
            'rate'     => (float) $s->get('rupees_per_coin', '1.00'),
            'requests' => $stmt->fetchAll(),
        ]);
    }

    public function submit(): void
    {
        $user = Auth::requireRole('customer');
        Csrf::enforce('/redeem');

        $coins = filter_var($_POST['coins'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 10000000]]);
        $error = $coins === false
            ? 'Enter a whole number of coins.'
            : (new RedeemService(Database::connection()))->request((int) $user['id'], $coins);

        Session::flash($error ? 'error' : 'success', $error ?? 'Request sent. Your coins are on hold until the owner reviews it.');
        Url::redirect('/redeem');
    }

    // ---- admin ----
    public function adminList(): void
    {
        $user = Auth::requireRole('admin');
        $rows = Database::connection()->query(
            "SELECT r.id, r.coins, r.rupees, r.status, r.created_at, u.name, u.email
             FROM redemption_requests r JOIN users u ON u.id = r.user_id
             ORDER BY (r.status = 'pending') DESC, r.id DESC LIMIT 100"
        )->fetchAll();

        View::render('admin/redemptions', [
            'title' => 'Redemptions', 'nav' => 'admin', 'active' => 'redemptions', 'user' => $user, 'rows' => $rows,
        ]);
    }

    public function decide(): void
    {
        $admin = Auth::requireRole('admin');
        Csrf::enforce('/admin/redemptions');

        $id     = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
        $action = $_POST['action'] ?? '';
        $svc    = new RedeemService(Database::connection());

        $ok = false;
        if ($id !== false && $action === 'approve') {
            $ok = $svc->approve($id, (int) $admin['id']);
        } elseif ($id !== false && $action === 'reject') {
            $ok = $svc->reject($id, (int) $admin['id']);
        }

        Session::flash($ok ? 'success' : 'error', $ok ? 'Request updated.' : 'Request not found, or it was already handled.');
        Url::redirect('/admin/redemptions');
    }
}