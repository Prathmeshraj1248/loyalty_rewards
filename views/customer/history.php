<?php
$labels = [
    'earn'          => ['Coins earned', 'text-emerald-600'],
    'redeem_hold'   => ['Redemption requested', 'text-amber-600'],
    'redeem_refund' => ['Redemption refunded', 'text-emerald-600'],
];
?>
<section class="rise">
  <h1 class="text-2xl font-bold text-slate-900">Transaction history</h1>
  <div class="mt-6 rounded-2xl bg-white p-2 shadow-sm ring-1 ring-slate-200 sm:p-6">
    <?php if (!$rows): ?>
      <div class="py-12 text-center">
        <p class="font-medium text-slate-700">No transactions yet</p>
        <p class="mt-1 text-sm text-slate-500">Everything you earn or redeem will be listed here.</p>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="text-xs uppercase tracking-wide text-slate-400">
            <tr><th class="px-3 py-2">Date</th><th class="px-3 py-2">Type</th><th class="px-3 py-2 text-right">Coins</th></tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php foreach ($rows as $row): [$label, $color] = $labels[$row['entry_type']]; ?>
              <tr>
                <td class="whitespace-nowrap px-3 py-3 text-slate-500"><?= $e(date('d M Y, h:i A', strtotime($row['created_at']))) ?></td>
                <td class="px-3 py-3"><?= $e($label) ?></td>
                <td class="px-3 py-3 text-right font-semibold <?= $color ?>"><?= $row['points'] > 0 ? '+' : '' ?><?= $e($row['points']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php if ($pages > 1): ?>
        <div class="mt-4 flex items-center justify-between px-3 text-sm">
          <span class="text-slate-500">Page <?= $e($page) ?> of <?= $e($pages) ?></span>
          <div class="flex gap-2">
            <?php if ($page > 1): ?><a class="rounded-lg px-3 py-1.5 ring-1 ring-slate-200 hover:bg-slate-50" href="<?= $e($url('/history?page=' . ($page - 1))) ?>">Previous</a><?php endif; ?>
            <?php if ($page < $pages): ?><a class="rounded-lg px-3 py-1.5 ring-1 ring-slate-200 hover:bg-slate-50" href="<?= $e($url('/history?page=' . ($page + 1))) ?>">Next</a><?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>