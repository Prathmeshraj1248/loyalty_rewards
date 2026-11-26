<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Http\Csrf;
use App\Http\Session;
use App\Http\Url;
use App\Http\View;
use App\Middleware\Auth;
use App\Repositories\CodeRepository;
use App\Services\CodeService;

final class CodeAdminController
{
    private CodeRepository $codes;

    public function __construct()
    {
        $this->codes = new CodeRepository(Database::connection());
    }

    public function index(): void
    {
        $user = Auth::requireRole('admin');
        $id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);

        View::render('admin/codes', [
            'title' => 'Codes', 'nav' => 'admin', 'active' => 'codes', 'user' => $user,
            'detail' => $id === false ? null : $this->codes->findDetailById($id),
        ]);
    }

    // POST so the plain code never appears in the URL, browser history or server logs.
    public function lookup(): void
    {
        Auth::requireRole('admin');
        Csrf::enforce('/admin/codes');

        $raw     = is_string($_POST['code'] ?? null) ? $_POST['code'] : '';
        $service = new CodeService();
        $code    = $service->normalize($raw);

        if (!$service->isWellFormed($code)) {
            Session::flash('error', 'That is not a valid code. Check the characters and the last letter.');
            Url::redirect('/admin/codes');
        }
        $row = $this->codes->findDetailByHash($service->hash($code));
        if ($row === null) {
            Session::flash('error', 'No code like that exists in the system.');
            Url::redirect('/admin/codes');
        }
        Url::redirect('/admin/codes?id=' . (int) $row['id']);
    }

    public function block(): void
    {
        Auth::requireRole('admin');
        Csrf::enforce('/admin/codes');
        $id = $this->codeId();

        $ok = $this->codes->block($id);
        Session::flash($ok ? 'success' : 'error', $ok ? 'Code blocked.' : 'Only unused codes can be blocked.');
        Url::redirect('/admin/codes?id=' . $id);
    }

    public function unblock(): void
    {
        Auth::requireRole('admin');
        Csrf::enforce('/admin/codes');
        $id = $this->codeId();

        $ok = $this->codes->unblock($id);
        Session::flash($ok ? 'success' : 'error', $ok ? 'Code unblocked.' : 'Only blocked, unclaimed codes can be unblocked.');
        Url::redirect('/admin/codes?id=' . $id);
    }

    public function blockBatch(): void
    {
        Auth::requireRole('admin');
        Csrf::enforce('/admin/batches');
        $batchId = filter_var($_POST['batch_id'] ?? '', FILTER_VALIDATE_INT);
        if ($batchId === false) {
            Session::flash('error', 'Batch not found.');
            Url::redirect('/admin/batches');
        }

        $count = $this->codes->blockBatch($batchId);
        Session::flash('success', "Blocked $count unused code(s) in batch #$batchId.");
        Url::redirect('/admin/batches');
    }

    private function codeId(): int
    {
        $id = filter_var($_POST['code_id'] ?? '', FILTER_VALIDATE_INT);
        if ($id === false) {
            Session::flash('error', 'Code not found.');
            Url::redirect('/admin/codes');
        }
        return $id;
    }
}