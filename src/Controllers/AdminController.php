<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Config\Env;
use App\Http\Csrf;
use App\Http\Session;
use App\Http\Url;
use App\Http\View;
use App\Middleware\Auth;
use App\Repositories\BatchRepository;
use App\Repositories\ProductRepository;
use App\Services\BatchService;
use App\Services\CodeService;
use App\Services\QrService;

final class AdminController
{
    private const MAX_BATCH = 500;

    private ProductRepository $products;
    private BatchRepository $batches;

    public function __construct()
    {
        $pdo = Database::connection();
        $this->products = new ProductRepository($pdo);
        $this->batches  = new BatchRepository($pdo);
    }

    // ---------- Products ----------

    public function products(): void
    {
        $user = Auth::requireRole('admin');
        View::render('admin/products', [
            'title' => 'Products', 'nav' => 'admin', 'active' => 'products',
            'user' => $user, 'products' => $this->products->all(),
        ]);
    }

    public function createProduct(): void
    {
        Auth::requireRole('admin');
        Csrf::enforce('/admin/products');
        [$name, $coins] = $this->readProductInput('/admin/products');

        $this->products->create($name, $coins);
        Session::flash('success', 'Product added.');
        Url::redirect('/admin/products');
    }

    public function updateProduct(): void
    {
        Auth::requireRole('admin');
        Csrf::enforce('/admin/products');
        $product = $this->requireProduct();
        [$name, $coins] = $this->readProductInput('/admin/products');

        $this->products->update((int) $product['id'], $name, $coins);
        Session::flash('success', 'Product updated. New claims will use the new coin value.');
        Url::redirect('/admin/products');
    }

    public function toggleProduct(): void
    {
        Auth::requireRole('admin');
        Csrf::enforce('/admin/products');
        $product = $this->requireProduct();

        $this->products->setActive((int) $product['id'], !$product['is_active']);
        Session::flash('success', $product['is_active'] ? 'Product deactivated.' : 'Product activated.');
        Url::redirect('/admin/products');
    }

    // ---------- Batches ----------

    public function batches(): void
    {
        $user = Auth::requireRole('admin');
        $activeProducts = array_values(array_filter($this->products->all(), fn ($p) => (bool) $p['is_active']));

        View::render('admin/batches', [
            'title' => 'Code batches', 'nav' => 'admin', 'active' => 'batches',
            'user' => $user, 'products' => $activeProducts,
            'batches' => $this->batches->recent(), 'maxBatch' => self::MAX_BATCH,
        ]);
    }

    public function generateBatch(): void
    {
        $admin = Auth::requireRole('admin');
        Csrf::enforce('/admin/batches');

        $quantity = filter_var($_POST['quantity'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => self::MAX_BATCH]]);
        $product  = $this->requireProduct('/admin/batches');

        if ($quantity === false) {
            Session::flash('error', 'Quantity must be a whole number from 1 to ' . self::MAX_BATCH . '.');
            Url::redirect('/admin/batches');
        }
        if (!$product['is_active']) {
            Session::flash('error', 'That product is deactivated. Activate it first.');
            Url::redirect('/admin/batches');
        }

        set_time_limit(120);
        try {
            $pdo     = Database::connection();
            $service = new BatchService($pdo, $this->batches, new CodeService());
            $result  = $service->generate((int) $product['id'], $quantity, (int) $admin['id']);
        } catch (\Throwable $e) {
            error_log('Batch generation failed: ' . $e->getMessage());
            Session::flash('error', 'Could not generate codes. Nothing was saved.');
            Url::redirect('/admin/batches');
        }

        // The plain codes are kept in the session only long enough to show the print sheet once.
        Session::put('sheet', ['batch_id' => $result['batch_id'], 'product' => $product['name'], 'codes' => $result['codes']]);
        Url::redirect('/admin/batches/sheet');
    }

    public function sheet(): void
    {
        $user = Auth::requireRole('admin');
        $data = Session::get('sheet');
        if (!is_array($data)) {
            Session::flash('error', 'That print sheet is only shown once and is no longer available.');
            Url::redirect('/admin/batches');
        }

        // Build every QR BEFORE discarding the codes, so a failure here does not lose them.
        $base  = rtrim((string) Env::get('APP_URL', ''), '/');
        $qr    = new QrService();
        $items = [];
        foreach ($data['codes'] as $code) {
            $items[] = ['code' => $code, 'qr' => $qr->dataUri($base . '/scan#' . $code)];
        }
        Session::forget('sheet');

        View::render('admin/sheet', [
            'title' => 'Print sheet', 'nav' => 'admin', 'active' => 'batches',
            'user' => $user, 'items' => $items, 'batchId' => $data['batch_id'], 'product' => $data['product'],
        ]);
    }

    // ---------- helpers ----------

    private function postString(string $key): string
    {
        $value = $_POST[$key] ?? '';
        return is_string($value) ? $value : '';
    }

    /** @return array{0: string, 1: int} */
    private function readProductInput(string $backTo): array
    {
        $name  = trim($this->postString('name'));
        $coins = filter_var($this->postString('coins'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100000]]);

        if (mb_strlen($name) < 2 || mb_strlen($name) > 150 || $coins === false) {
            Session::flash('error', 'Enter a product name (2 to 150 characters) and coins from 1 to 100000.');
            Url::redirect($backTo);
        }
        return [$name, $coins];
    }

    private function requireProduct(string $backTo = '/admin/products'): array
    {
        $id = filter_var($this->postString('product_id'), FILTER_VALIDATE_INT);
        $product = $id === false ? null : $this->products->find($id);
        if ($product === null) {
            Session::flash('error', 'Product not found.');
            Url::redirect($backTo);
        }
        return $product;
    }
}