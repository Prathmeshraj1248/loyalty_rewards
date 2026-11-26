<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Http\View;
use App\Middleware\Auth;

final class DashboardController
{
    public function customer(): void
    {
        $user = Auth::requireRole('customer');
        View::render('customer/dashboard', ['title' => 'My dashboard', 'user' => $user]);
    }

    public function admin(): void
    {
        $user = Auth::requireRole('admin');
        View::render('admin/dashboard', ['title' => 'Admin dashboard', 'user' => $user]);
    }
}