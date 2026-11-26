<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Http\View;
use App\Middleware\Auth;

final class DashboardController
{
    public function admin(): void
    {
        $user = Auth::requireRole('admin');
        View::render('admin/dashboard', ['title' => 'Admin dashboard', 'nav' => 'admin', 'active' => 'home', 'user' => $user]);
    }
}