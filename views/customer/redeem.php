<?php
use App\Http\Csrf;
$badge = ['pending' => 'bg-amber-50 text-amber-700', 'approved' => 'bg-emerald-50 text-emerald-700', 'rejected' => 'bg-rose-50 text-rose-700'];
?>
<section class="rise">
  <h1 class="text-2xl font-bold text-slate-900">Redeem coins</h1>
  <p class="mt-1 text-slate-500">You have <b><?= $e(number_format($balance)) ?></b> coins. 1 coin = ₹<?= $e(number_format($rate, 2)) ?>. Minimum <?= $e($min) ?> coins.</p>

  <form method="post" action="<?= $e($url('/redeem')) ?>" class="mt-6 flex flex-wrap items-end gap-3 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <?= Csrf::field() ?>
    <label class="text-sm font-medium text-slate-700">Coins to redeem
      <input type="number" name="coins" required min="<?= $e($min) ?>" max="<?= $e($balance) ?>" class="mt-1.5 block w-48 rounded-xl border border-slate-300 px-3 py-2 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
    </label>
    <button class="rounded-xl bg-indigo-600 px-5 py-2 font-semibold text-white transition hover:bg-indigo-700">Request</button>
  </form>

  <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <h2 class="font-semibold text-slate-900">Your requests</h2>
    <?php if (!$requests): ?>
      <p class="py-8 text-center text-sm text-slate-500">No requests yet.</p>
    <?php else: ?>
      <ul class="mt-3 divide-y divide-slate-100">
        <?php foreach ($requests as $r): ?>
          <li class="flex items-center justify-between py-3 text-sm">
            <div>
              <p class="font-medium"><?= $e(number_format((int) $r['coins'])) ?> coins = ₹<?= $e(number_format((float) $r['rupees'], 2)) ?></p>
              <p class="text-xs text-slate-400"><?= $e(date('d M Y, h:i A', strtotime($r['created_at']))) ?></p>
            </div>
            <span class="rounded-full px-2.5 py-1 text-xs font-semibold <?= $badge[$r['status']] ?>"><?= $e(ucfirst($r['status'])) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</section>