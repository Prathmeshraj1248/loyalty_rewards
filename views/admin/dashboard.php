<?php
$resultLabels = [
    'success'        => ['Success', 'bg-emerald-50 text-emerald-700'],
    'invalid'        => ['Invalid code', 'bg-rose-50 text-rose-700'],
    'already_claimed' => ['Already claimed', 'bg-amber-50 text-amber-700'],
    'blocked'        => ['Blocked', 'bg-rose-50 text-rose-700'],
    'rate_limited'   => ['Rate limited', 'bg-slate-100 text-slate-700'],
];
$cards = [
    ['Customers', $summary['customers'], 'text-slate-900'],
    ['Coins issued', $summary['coins_issued'], 'text-indigo-600'],
    ['Codes unused', $summary['codes_unused'], 'text-slate-900'],
    ['Codes claimed', $summary['codes_claimed'], 'text-emerald-600'],
    ['Codes blocked', $summary['codes_blocked'], 'text-rose-600'],
    ['Failed attempts (7 days)', $summary['failed_7d'], 'text-amber-600'],
];
?>
<section class="rise">
  <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
  <p class="mt-1 text-slate-500">Live overview of your rewards programme.</p>

  <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-3">
    <?php foreach ($cards as [$label, $value, $color]): ?>
      <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <p class="text-sm text-slate-500"><?= $e($label) ?></p>
        <p class="mt-2 text-3xl font-bold <?= $color ?>"><?= $e(number_format($value)) ?></p>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <h2 class="font-semibold text-slate-900">Scan attempts, last 7 days</h2>
    <?php if (!$daily['hasData']): ?>
      <div class="py-10 text-center">
        <p class="font-medium text-slate-700">No scan attempts yet</p>
        <p class="mt-1 text-sm text-slate-500">The chart appears when customers start scanning codes.</p>
      </div>
    <?php else: ?>
      <div class="mt-4 h-64"><canvas id="dailyChart"></canvas></div>
      <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
      <script>
        const daily = <?= json_encode($daily, JSON_HEX_TAG | JSON_HEX_AMP) ?>;
        new Chart(document.getElementById('dailyChart'), {
          type: 'bar',
          data: {
            labels: daily.labels,
            datasets: [
              { label: 'Successful', data: daily.success, backgroundColor: '#10b981', borderRadius: 6 },
              { label: 'Failed', data: daily.failed, backgroundColor: '#f43f5e', borderRadius: 6 }
            ]
          },
          options: { maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
      </script>
    <?php endif; ?>
  </div>

  <div class="mt-6 grid gap-6 lg:grid-cols-2">
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
      <h2 class="font-semibold text-slate-900">Recent attempts</h2>
      <?php if (!$attempts): ?>
        <p class="py-8 text-center text-sm text-slate-500">Nothing to show yet.</p>
      <?php else: ?>
        <ul class="mt-3 divide-y divide-slate-100">
          <?php foreach ($attempts as $a): [$label, $badge] = $resultLabels[$a['result']]; ?>
            <li class="flex items-center justify-between gap-3 py-3 text-sm">
              <div class="min-w-0">
                <p class="truncate text-slate-800"><?= $e($a['email'] ?? 'Not logged in') ?></p>
                <p class="text-xs text-slate-400"><?= $e($a['ip_address']) ?> · <?= $e(date('d M, h:i A', strtotime($a['created_at']))) ?></p>
              </div>
              <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold <?= $badge ?>"><?= $e($label) ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
      <h2 class="font-semibold text-slate-900">Top customers by balance</h2>
      <?php if (!$top): ?>
        <p class="py-8 text-center text-sm text-slate-500">No customer has earned coins yet.</p>
      <?php else: ?>
        <ol class="mt-3 divide-y divide-slate-100">
          <?php foreach ($top as $i => $t): ?>
            <li class="flex items-center justify-between py-3 text-sm">
              <div class="min-w-0">
                <p class="truncate font-medium text-slate-800"><?= $e($i + 1) ?>. <?= $e($t['name']) ?></p>
                <p class="truncate text-xs text-slate-400"><?= $e($t['email']) ?></p>
              </div>
              <span class="font-semibold text-indigo-600"><?= $e(number_format((int) $t['balance'])) ?></span>
            </li>
          <?php endforeach; ?>
        </ol>
      <?php endif; ?>
    </div>
  </div>
</section>
  <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <div class="flex items-center justify-between">
      <h2 class="font-semibold text-slate-900">Security insights (last 24 hours)</h2>
      <form method="post" action="<?= $e($url('/admin/insights')) ?>">
        <?= \App\Http\Csrf::field() ?>
        <button class="rounded-xl px-4 py-2 text-sm font-semibold text-indigo-600 ring-1 ring-indigo-200 transition hover:bg-indigo-50">Summarise with AI</button>
      </form>
    </div>
    <?php if (!$flags): ?>
      <p class="py-6 text-center text-sm text-slate-500">No suspicious activity detected.</p>
    <?php else: ?>
      <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-slate-700">
        <?php foreach ($flags as $f): ?><li><?= $e($f) ?></li><?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <?php if ($aiSummary): ?>
      <div class="mt-4 rounded-xl bg-indigo-50 p-4 text-sm text-slate-800">
        <p class="text-xs font-semibold uppercase text-indigo-600"><?= $e($aiSummary['source']) ?> summary</p>
        <p class="mt-1 whitespace-pre-line"><?= $e($aiSummary['text']) ?></p>
      </div>
    <?php endif; ?>
  </div>