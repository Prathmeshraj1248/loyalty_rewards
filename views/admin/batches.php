<?php
use App\Http\Csrf;
$input = 'mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100';
?>
<section class="rise">
  <h1 class="text-2xl font-bold text-slate-900">Code batches</h1>
  <p class="mt-1 text-slate-500">Generate unique codes for a production run, then print the QR stickers.</p>

  <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <h2 class="font-semibold text-slate-900">Generate a new batch</h2>
    <?php if (!$products): ?>
      <p class="mt-3 text-sm text-slate-500">You need an active product first.
        <a class="font-semibold text-indigo-600 hover:underline" href="<?= $e($url('/admin/products')) ?>">Add one</a>.</p>
    <?php else: ?>
      <form method="post" action="<?= $e($url('/admin/batches/generate')) ?>" class="mt-4 grid items-end gap-4 sm:grid-cols-[1fr_160px_auto]">
        <?= Csrf::field() ?>
        <label class="text-sm font-medium text-slate-700">Product
          <select name="product_id" required class="<?= $input ?>">
            <?php foreach ($products as $p): ?>
              <option value="<?= $e($p['id']) ?>"><?= $e($p['name']) ?> (<?= $e($p['coins_per_unit']) ?> coins)</option>
            <?php endforeach; ?>
          </select>
        </label>
        <label class="text-sm font-medium text-slate-700">Quantity
          <input type="number" name="quantity" required min="1" max="<?= $e($maxBatch) ?>" value="10" class="<?= $input ?>">
        </label>
        <button class="rounded-xl bg-indigo-600 px-5 py-2 font-semibold text-white transition hover:bg-indigo-700">Generate</button>
      </form>
      <p class="mt-3 text-xs text-amber-700">The codes are shown once on the next page. Print it or save it as PDF straight away. They cannot be shown again.</p>
    <?php endif; ?>
  </div>

  <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <h2 class="font-semibold text-slate-900">Previous batches</h2>
    <?php if (!$batches): ?>
      <div class="py-10 text-center">
        <p class="font-medium text-slate-700">No batches yet</p>
        <p class="mt-1 text-sm text-slate-500">Generated batches will be listed here.</p>
      </div>
    <?php else: ?>
      <div class="mt-4 overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="text-xs uppercase tracking-wide text-slate-400">
            <tr><th class="px-3 py-2">Batch</th><th class="px-3 py-2">Product</th><th class="px-3 py-2">Created</th>
                <th class="px-3 py-2 text-right">Unused</th><th class="px-3 py-2 text-right">Claimed</th><th class="px-3 py-2 text-right">Blocked</th></tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php foreach ($batches as $b): ?>
              <tr>
                <td class="px-3 py-3 font-medium">#<?= $e($b['id']) ?></td>
                <td class="px-3 py-3"><?= $e($b['product_name']) ?></td>
                <td class="whitespace-nowrap px-3 py-3 text-slate-500"><?= $e(date('d M Y, h:i A', strtotime($b['created_at']))) ?></td>
                <td class="px-3 py-3 text-right"><?= $e((int) $b['unused']) ?></td>
                <td class="px-3 py-3 text-right text-emerald-600"><?= $e((int) $b['claimed']) ?></td>
                <td class="px-3 py-3 text-right text-rose-600"><?= $e((int) $b['blocked']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>