<?php
use App\Http\Csrf;
use App\Http\Session;

$flash  = Session::pullFlash();
$nav    = $nav ?? null;      // 'customer', 'admin' or null (logged out)
$active = $active ?? '';

$links = match ($nav) {
    'customer' => ['home' => ['/dashboard', 'Home'], 'claim' => ['/claim', 'Claim coins'], 'history' => ['/history', 'History']],
      'admin'    => [
        'home'      => ['/admin', 'Dashboard'],
        'products'  => ['/admin/products', 'Products'],
        'batches'   => ['/admin/batches', 'Code batches'],
        'codes'     => ['/admin/codes', 'Codes'],
        'customers' => ['/admin/customers', 'Customers'],
    ],
    default    => [],
};
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $e($title ?? 'Loyalty Rewards') ?></title>
  <script src="https://cdn.tailwindcss.com/3.4.17"></script>
  <style>
    @keyframes rise { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
    .rise { animation: rise .35s ease-out both; }
  </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
  <?php if ($nav): ?>
  <header class="sticky top-0 z-10 border-b border-slate-200 bg-white/90 backdrop-blur">
    <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-3">
      <div class="flex items-center gap-6">
        <span class="text-lg font-bold text-indigo-600">Loyalty<span class="text-slate-800">Rewards</span></span>
        <nav class="flex gap-1">
          <?php foreach ($links as $key => [$path, $label]): ?>
            <a href="<?= $e($url($path)) ?>"
               class="rounded-lg px-3 py-1.5 text-sm font-medium transition <?= $active === $key ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100' ?>">
              <?= $e($label) ?>
            </a>
          <?php endforeach; ?>
        </nav>
      </div>
      <form method="post" action="<?= $e($url('/logout')) ?>">
        <?= Csrf::field() ?>
        <button class="rounded-lg px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100">Log out</button>
      </form>
    </div>
  </header>
  <?php endif; ?>

  <main class="mx-auto max-w-5xl px-4 py-8">
    <?php if ($flash): ?>
      <div class="rise mx-auto mb-6 max-w-md rounded-xl px-4 py-3 text-sm <?= $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-800' : 'bg-rose-50 text-rose-800' ?>">
        <?= $e($flash['message']) ?>
      </div>
    <?php endif; ?>
    <?= $content ?>
  </main>
</body>
</html>