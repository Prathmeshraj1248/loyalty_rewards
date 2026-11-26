<?php
use App\Http\Csrf;
$input = 'mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100';
?>
<section class="rise">
  <h1 class="text-2xl font-bold text-slate-900">Products</h1>
  <p class="mt-1 text-slate-500">Each box of a product earns the customer a fixed number of coins.</p>

  <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <h2 class="font-semibold text-slate-900">Add a product</h2>
    <form method="post" action="<?= $e($url('/admin/products/create')) ?>" class="mt-4 grid items-end gap-4 sm:grid-cols-[1fr_160px_auto]">
      <?= Csrf::field() ?>
      <label class="text-sm font-medium text-slate-700">Name
        <input type="text" name="name" required minlength="2" maxlength="150" class="<?= $input ?>">
      </label>
      <label class="text-sm font-medium text-slate-700">Coins per box
        <input type="number" name="coins" required min="1" max="100000" class="<?= $input ?>">
      </label>
      <button class="rounded-xl bg-indigo-600 px-5 py-2 font-semibold text-white transition hover:bg-indigo-700">Add</button>
    </form>
  </div>

  <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <h2 class="font-semibold text-slate-900">All products</h2>
    <?php if (!$products): ?>
      <div class="py-10 text-center">
        <p class="font-medium text-slate-700">No products yet</p>
        <p class="mt-1 text-sm text-slate-500">Add your first product above to start generating codes.</p>
      </div>
    <?php else: ?>
      <ul class="mt-4 divide-y divide-slate-100">
        <?php foreach ($products as $p): ?>
          <li class="flex flex-wrap items-end gap-3 py-4 <?= $p['is_active'] ? '' : 'opacity-60' ?>">
            <form method="post" action="<?= $e($url('/admin/products/update')) ?>" class="flex flex-1 flex-wrap items-end gap-3">
              <?= Csrf::field() ?>
              <input type="hidden" name="product_id" value="<?= $e($p['id']) ?>">
              <label class="min-w-[180px] flex-1 text-xs font-medium text-slate-500">Name
                <input type="text" name="name" value="<?= $e($p['name']) ?>" required minlength="2" maxlength="150" class="<?= $input ?>">
              </label>
              <label class="w-32 text-xs font-medium text-slate-500">Coins
                <input type="number" name="coins" value="<?= $e($p['coins_per_unit']) ?>" required min="1" max="100000" class="<?= $input ?>">
              </label>
              <button class="rounded-xl px-4 py-2 text-sm font-semibold text-indigo-600 ring-1 ring-indigo-200 transition hover:bg-indigo-50">Save</button>
            </form>
            <form method="post" action="<?= $e($url('/admin/products/toggle')) ?>">
              <?= Csrf::field() ?>
              <input type="hidden" name="product_id" value="<?= $e($p['id']) ?>">
              <button class="rounded-xl px-4 py-2 text-sm font-semibold ring-1 transition <?= $p['is_active'] ? 'text-rose-600 ring-rose-200 hover:bg-rose-50' : 'text-emerald-600 ring-emerald-200 hover:bg-emerald-50' ?>">
                <?= $p['is_active'] ? 'Deactivate' : 'Activate' ?>
              </button>
            </form>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</section>