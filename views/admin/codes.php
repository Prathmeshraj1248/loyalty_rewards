<?php
use App\Http\Csrf;
$badges = [
    'unused'  => 'bg-slate-100 text-slate-700',
    'claimed' => 'bg-emerald-50 text-emerald-700',
    'blocked' => 'bg-rose-50 text-rose-700',
];
?>
<section class="rise">
  <h1 class="text-2xl font-bold text-slate-900">Code lookup</h1>
  <p class="mt-1 text-slate-500">Type a code from a box to see its status, or block it.</p>

  <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <form method="post" action="<?= $e($url('/admin/codes/lookup')) ?>" class="flex flex-wrap items-end gap-3">
      <?= Csrf::field() ?>
      <label class="min-w-[240px] flex-1 text-sm font-medium text-slate-700">Code
        <input type="text" name="code" required maxlength="30" placeholder="K7QM-3WPX-9TRD-H" autocomplete="off"
               class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2 font-mono uppercase outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
      </label>
      <button class="rounded-xl bg-indigo-600 px-5 py-2 font-semibold text-white transition hover:bg-indigo-700">Look up</button>
    </form>
  </div>

  <?php if ($detail): ?>
    <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-slate-900">Code #<?= $e($detail['id']) ?></h2>
        <span class="rounded-full px-3 py-1 text-xs font-semibold <?= $badges[$detail['status']] ?>"><?= $e(ucfirst($detail['status'])) ?></span>
      </div>
      <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
        <div><dt class="text-slate-400">Product</dt><dd class="font-medium"><?= $e($detail['product_name']) ?> (<?= $e($detail['coins_per_unit']) ?> coins)</dd></div>
        <div><dt class="text-slate-400">Batch</dt><dd class="font-medium">#<?= $e($detail['batch_id']) ?></dd></div>
        <div><dt class="text-slate-400">Created</dt><dd class="font-medium"><?= $e(date('d M Y, h:i A', strtotime($detail['created_at']))) ?></dd></div>
        <?php if ($detail['claimed_email']): ?>
          <div><dt class="text-slate-400">Claimed by</dt><dd class="font-medium"><?= $e($detail['claimed_name']) ?> (<?= $e($detail['claimed_email']) ?>)</dd></div>
          <div><dt class="text-slate-400">Claimed at</dt><dd class="font-medium"><?= $e(date('d M Y, h:i A', strtotime($detail['claimed_at']))) ?></dd></div>
        <?php endif; ?>
      </dl>

      <?php if ($detail['status'] === 'unused'): ?>
        <form method="post" action="<?= $e($url('/admin/codes/block')) ?>" class="mt-5">
          <?= Csrf::field() ?><input type="hidden" name="code_id" value="<?= $e($detail['id']) ?>">
          <button class="rounded-xl px-4 py-2 text-sm font-semibold text-rose-600 ring-1 ring-rose-200 transition hover:bg-rose-50">Block this code</button>
        </form>
      <?php elseif ($detail['status'] === 'blocked'): ?>
        <form method="post" action="<?= $e($url('/admin/codes/unblock')) ?>" class="mt-5">
          <?= Csrf::field() ?><input type="hidden" name="code_id" value="<?= $e($detail['id']) ?>">
          <button class="rounded-xl px-4 py-2 text-sm font-semibold text-emerald-600 ring-1 ring-emerald-200 transition hover:bg-emerald-50">Unblock this code</button>
        </form>
      <?php else: ?>
        <p class="mt-5 text-sm text-slate-500">A claimed code cannot be changed. The customer already received the coins.</p>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</section>