<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Http\Csrf;
use App\Http\Session;
use App\Http\Url;
use App\Http\View;
use App\Middleware\Auth;
use App\Repositories\ClaimRepository;
use App\Services\ClaimService;
use App\Services\CodeService;

final class ClaimController
{
    // GET /claim : show the form
    public function show(): void
    {
        $user = Auth::requireRole('customer');

        View::render('customer/claim', [
            'title' => 'Claim coins', 'nav' => 'customer', 'active' => 'claim', 'user' => $user,
        ]);
    }

    // POST /claim : hand the code to the service and show the outcome
    public function submit(): void
    {
        $user = Auth::requireRole('customer');
        Csrf::enforce('/claim');

        $raw = is_string($_POST['code'] ?? null) ? $_POST['code'] : '';
        $raw = mb_substr($raw, 0, 40); // a real code is 16 characters with dashes

        $service = new ClaimService(new ClaimRepository(Database::connection()), new CodeService());
        // REMOTE_ADDR is set by the web server. Headers like X-Forwarded-For can be faked by the client.
        $result = $service->claim((int) $user['id'], $raw, $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');

        if ($result['ok']) {
            Session::flash('success', $result['message']);
            Url::redirect('/dashboard');
        }
        Session::flash('error', $result['message']);
        Url::redirect('/claim');
    }
}
