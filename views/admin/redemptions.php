<?php use App\Http\Csrf; ?>
<section class="rise">
  <h1 class="text-2xl font-bold text-slate-900">Redemption requests</h1>
  <p class="mt-1 text-slate-500">Approve means you pay the customer outside this app (payout is simulated).</p>
  <div class="mt-6 overflow-x-auto rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
    <?php if (!$rows): ?>
      <p class="py-10 text-center text-slate-500">No requests yet.</p>
    <?php else: ?>
      <table class="w-full text-left text-sm">
        <thead class="text-xs uppercase text-slate-400">
          <tr><th class="p-2">Customer</th><th class="p-2">Date</th><th class="p-2 text-right">Coins</th><th class="p-2 text-right">Pay (₹)</th><th class="p-2">Status</th><th class="p-2"></th></tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach ($rows as $r): ?>
            <tr>
              <td class="p-2"><?= $e($r['name']) ?><br><span class="text-xs text-slate-400"><?= $e($r['email']) ?></span></td>
              <td class="whitespace-nowrap p-2 text-slate-500"><?= $e(date('d M Y, h:i A', strtotime($r['created_at']))) ?></td>
              <td class="p-2 text-right"><?= $e(number_format((int) $r['coins'])) ?></td>
              <td class="p-2 text-right font-semibold"><?= $e(number_format((float) $r['rupees'], 2)) ?></td>
              <td class="p-2"><?= $e(ucfirst($r['status'])) ?></td>
              <td class="p-2">
                <?php if ($r['status'] === 'pending'): ?>
                  <form method="post" action="<?= $e($url('/admin/redemptions')) ?>" class="flex justify-end gap-3">
                    <?= Csrf::field() ?><input type="hidden" name="id" value="<?= $e($r['id']) ?>">
                    <button name="action" value="approve" class="font-semibold text-emerald-600 hover:underline">Approve</button>
                    <button name="action" value="reject" class="font-semibold text-rose-600 hover:underline">Reject</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</section>