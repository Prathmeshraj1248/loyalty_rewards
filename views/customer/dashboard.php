<?php
$labels = [
    'earn'          => ['Coins earned', 'text-emerald-600'],
    'redeem_hold'   => ['Redemption requested', 'text-amber-600'],
    'redeem_refund' => ['Redemption refunded', 'text-emerald-600'],
];
?>
<section class="rise">
  <h1 class="text-2xl font-bold text-slate-900">Hello, <?= $e($user['name']) ?></h1>
  <p class="mt-1 text-slate-500">Here is your rewards summary.</p>

  <div class="mt-6 grid gap-4 sm:grid-cols-2">
    <div class="rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 p-6 text-white shadow-lg">
      <p class="text-sm text-indigo-100">Your coins</p>
      <p class="mt-2 text-5xl font-bold"><?= $e(number_format($balance)) ?></p>
    </div>
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
      <p class="text-sm text-slate-500">Worth in rupees</p>
      <p class="mt-2 text-5xl font-bold text-slate-900">₹<?= $e(number_format($rupees, 2)) ?></p>
    </div>
  </div>

  <div class="mt-8 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <div class="flex items-center justify-between">
      <h2 class="font-semibold text-slate-900">Recent activity</h2>
      <a href="<?= $e($url('/history')) ?>" class="text-sm font-medium text-indigo-600 hover:underline">View all</a>
    </div>

    <?php if (!$recent): ?>
      <div class="py-10 text-center">
        <p class="font-medium text-slate-700">No activity yet</p>
        <p class="mt-1 text-sm text-slate-500">Coins you earn will appear here.</p>
      </div>
    <?php else: ?>
      <ul class="mt-4 divide-y divide-slate-100">
        <?php foreach ($recent as $row): [$label, $color] = $labels[$row['entry_type']]; ?>
          <li class="flex items-center justify-between py-3">
            <div>
              <p class="text-sm font-medium text-slate-800"><?= $e($label) ?></p>
              <p class="text-xs text-slate-400"><?= $e(date('d M Y, h:i A', strtotime($row['created_at']))) ?></p>
            </div>
            <span class="font-semibold <?= $color ?>"><?= $row['points'] > 0 ? '+' : '' ?><?= $e($row['points']) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</section>