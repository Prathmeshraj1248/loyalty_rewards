<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Http\Csrf;
use App\Http\Session;
use App\Http\Url;
use App\Http\View;
use App\Middleware\Auth;
use App\Repositories\UserRepository;

final class CustomerAdminController
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository(Database::connection());
    }

    // GET /admin/customers : list customers, optionally filtered by ?q=
    public function index(): void
    {
        $user   = Auth::requireRole('admin');
        $search = trim(is_string($_GET['q'] ?? null) ? $_GET['q'] : '');
        $search = mb_substr($search, 0, 100);

        View::render('admin/customers', [
            'title' => 'Customers', 'nav' => 'admin', 'active' => 'customers', 'user' => $user,
            'search'    => $search,
            'customers' => $this->users->customers($search),
        ]);
    }

    // POST /admin/customers/toggle : block an active customer, or unblock a blocked one
    public function toggle(): void
    {
        Auth::requireRole('admin');
        Csrf::enforce('/admin/customers');

        $id = filter_var($_POST['user_id'] ?? '', FILTER_VALIDATE_INT);
        $target = $id === false ? null : $this->users->findById($id);

        // Only customer accounts can be toggled, so an admin can never lock out an admin.
        if ($target === null || $target['role'] !== 'customer') {
            Session::flash('error', 'Customer not found.');
            Url::redirect('/admin/customers');
        }

        $newStatus = $target['status'] === 'active' ? 'blocked' : 'active';
        $this->users->setStatus((int) $target['id'], $newStatus);

        Session::flash('success', $newStatus === 'blocked' ? 'Customer blocked.' : 'Customer unblocked.');
        Url::redirect('/admin/customers');
    }
}
